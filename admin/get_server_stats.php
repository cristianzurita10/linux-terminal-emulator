<?php
session_start();

// Verificar si el usuario está logueado y es admin
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["es_admin"]) || $_SESSION["es_admin"] != 1){
    header('Content-Type: application/json');
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

// Habilitar depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión a la base de datos
require_once "../config.php";

// Funciones auxiliares
function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

function format_uptime($seconds) {
    $days = floor($seconds / 86400);
    $seconds %= 86400;
    $hours = floor($seconds / 3600);
    $seconds %= 3600;
    $minutes = floor($seconds / 60);
    $seconds %= 60;
    
    $uptime = "";
    if ($days > 0) $uptime .= "$days días, ";
    $uptime .= sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    
    return $uptime;
}

// Array para registrar datos
$data = [];
$debug = [];

// Obtener todas las variables de estado global
$status = [];
try {
    $result = mysqli_query($conn, "SHOW GLOBAL STATUS");
    if (!$result) {
        $debug[] = "Error en SHOW GLOBAL STATUS: " . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_array($result)) {
            $status[$row[0]] = $row[1];
        }
    }
} catch (Exception $e) {
    $debug[] = "Excepción en SHOW GLOBAL STATUS: " . $e->getMessage();
}

// Obtener variables del sistema
$variables = [];
try {
    $result = mysqli_query($conn, "SHOW VARIABLES");
    if (!$result) {
        $debug[] = "Error en SHOW VARIABLES: " . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_array($result)) {
            $variables[$row[0]] = $row[1];
        }
    }
} catch (Exception $e) {
    $debug[] = "Excepción en SHOW VARIABLES: " . $e->getMessage();
}

// Obtener información del servidor MySQL
try {
    $result = mysqli_query($conn, "SELECT VERSION() as version");
    if (!$result) {
        $debug[] = "Error al obtener VERSION: " . mysqli_error($conn);
    } else {
        $row = mysqli_fetch_assoc($result);
        $mysql_version = $row['version'];
    }
} catch (Exception $e) {
    $debug[] = "Excepción al obtener VERSION: " . $e->getMessage();
    $mysql_version = "Desconocido";
}

// Calcular uso de memoria de MySQL
$memory_used = 0;
$memory_items = [
    'innodb_buffer_pool_size',
    'key_buffer_size',
    'query_cache_size',
    'tmp_table_size',
    'innodb_log_buffer_size',
    'max_connections',
    'read_buffer_size',
    'read_rnd_buffer_size',
    'sort_buffer_size',
    'join_buffer_size'
];

foreach ($memory_items as $item) {
    if (isset($variables[$item])) {
        if ($item == 'max_connections') {
            // Para max_connections multiplicamos por los buffers por conexión
            $per_connection = 0;
            if (isset($variables['read_buffer_size'])) $per_connection += intval($variables['read_buffer_size']);
            if (isset($variables['read_rnd_buffer_size'])) $per_connection += intval($variables['read_rnd_buffer_size']);
            if (isset($variables['sort_buffer_size'])) $per_connection += intval($variables['sort_buffer_size']);
            if (isset($variables['join_buffer_size'])) $per_connection += intval($variables['join_buffer_size']);
            if (isset($variables['thread_stack'])) $per_connection += intval($variables['thread_stack']);
            
            $memory_used += intval($variables[$item]) * $per_connection;
        } else {
            $memory_used += intval($variables[$item]);
        }
    }
}

// Estimación de uso de CPU basada en carga de consultas y conexiones
// Esto es una aproximación muy simple
$cpu_usage = 0;
if (isset($status['Threads_running']) && isset($status['Uptime'])) {
    $threads_running = intval($status['Threads_running']);
    $questions = isset($status['Questions']) ? intval($status['Questions']) : 0;
    
    // Calculamos consultas por segundo para tener una idea de la carga
    $uptime = max(1, intval($status['Uptime'])); // evitar división por cero
    $qps = $questions / $uptime;
    
    // Fórmula muy simplificada para una estimación de CPU
    // Esta es una aproximación y no representa el uso real de CPU del sistema
    // Se basa en la cantidad de hilos y consultas por segundo
    $cpu_usage = min(100, ($threads_running * 5) + ($qps / 10));
}

// Datos para mostrar en el panel
$data['uptime'] = isset($status['Uptime']) ? format_uptime($status['Uptime']) : 'N/A';
$data['connections'] = isset($status['Threads_connected']) ? $status['Threads_connected'] : 'N/A';
$data['queries_per_second'] = isset($status['Queries'], $status['Uptime']) && intval($status['Uptime']) > 0 ? 
    round(intval($status['Queries']) / intval($status['Uptime']), 2) : 'N/A';
$data['memory_usage'] = format_bytes($memory_used);
$data['cpu_usage'] = round($cpu_usage, 1) . '%';

// Datos para gráficas
$current_time = date('H:i:s');
$data['ram_data'] = [
    'time' => $current_time,
    'value' => $memory_used / (1024 * 1024) // Convertir a MB
];

$data['cpu_data'] = [
    'time' => $current_time,
    'value' => $cpu_usage
];

// Variables del sistema para la tabla
$data['system_variables'] = [
    'MySQL Version' => $mysql_version ?? 'N/A',
    'Uptime' => $data['uptime'],
    'Threads Connected' => isset($status['Threads_connected']) ? $status['Threads_connected'] : 'N/A',
    'Threads Running' => isset($status['Threads_running']) ? $status['Threads_running'] : 'N/A',
    'Maximum Allowed Connections' => isset($variables['max_connections']) ? $variables['max_connections'] : 'N/A',
    'Open Tables' => isset($status['Open_tables']) ? $status['Open_tables'] : 'N/A',
    'Queries Per Second' => $data['queries_per_second'],
    'Table Opens' => isset($status['Opened_tables']) ? $status['Opened_tables'] : 'N/A',
    'Table Locks Waited' => isset($status['Table_locks_waited']) ? $status['Table_locks_waited'] : 'N/A',
    'Slow Queries' => isset($status['Slow_queries']) ? $status['Slow_queries'] : 'N/A'
];

// Estadísticas InnoDB
$data['innodb_status'] = [
    'Buffer Pool Size' => isset($variables['innodb_buffer_pool_size']) ? format_bytes(intval($variables['innodb_buffer_pool_size'])) : 'N/A',
    'Buffer Pool Pages Data' => isset($status['Innodb_buffer_pool_pages_data']) ? $status['Innodb_buffer_pool_pages_data'] : 'N/A',
    'Buffer Pool Pages Free' => isset($status['Innodb_buffer_pool_pages_free']) ? $status['Innodb_buffer_pool_pages_free'] : 'N/A',
    'Buffer Pool Read Requests' => isset($status['Innodb_buffer_pool_read_requests']) ? $status['Innodb_buffer_pool_read_requests'] : 'N/A',
    'Buffer Pool Reads' => isset($status['Innodb_buffer_pool_reads']) ? $status['Innodb_buffer_pool_reads'] : 'N/A',
    'Row Lock Time' => isset($status['Innodb_row_lock_time']) ? $status['Innodb_row_lock_time'] . ' ms' : 'N/A',
    'Row Lock Waits' => isset($status['Innodb_row_lock_waits']) ? $status['Innodb_row_lock_waits'] : 'N/A',
    'Pages Created' => isset($status['Innodb_pages_created']) ? $status['Innodb_pages_created'] : 'N/A',
    'Pages Read' => isset($status['Innodb_pages_read']) ? $status['Innodb_pages_read'] : 'N/A',
    'Pages Written' => isset($status['Innodb_pages_written']) ? $status['Innodb_pages_written'] : 'N/A'
];

// Obtener tamaños de bases de datos
$databases = [];
try {
    $result = mysqli_query($conn, "SHOW DATABASES");
    if (!$result) {
        $debug[] = "Error en SHOW DATABASES: " . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_array($result)) {
            $db_name = $row[0];
            
            // Saltar bases de datos del sistema
            if ($db_name == 'information_schema' || $db_name == 'performance_schema' || $db_name == 'mysql' || $db_name == 'sys') {
                continue;
            }
            
            // Obtener tamaño
            $size_query = "SELECT 
                            TABLE_SCHEMA AS 'Database',
                            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)',
                            COUNT(DISTINCT TABLE_NAME) AS 'Tables'
                          FROM information_schema.TABLES
                          WHERE TABLE_SCHEMA = '$db_name'
                          GROUP BY TABLE_SCHEMA";
            
            $size_result = mysqli_query($conn, $size_query);
            if ($size_result && $size_row = mysqli_fetch_array($size_result)) {
                $databases[] = [
                    'name' => $db_name,
                    'size' => $size_row['Size (MB)'] . ' MB',
                    'tables' => $size_row['Tables']
                ];
            } else {
                $debug[] = "Error al obtener tamaño de $db_name: " . mysqli_error($conn);
                $databases[] = [
                    'name' => $db_name,
                    'size' => 'N/A',
                    'tables' => 'N/A'
                ];
            }
        }
    }
} catch (Exception $e) {
    $debug[] = "Excepción en SHOW DATABASES: " . $e->getMessage();
}

$data['databases'] = $databases;
$data['debug'] = $debug;

// Devolver JSON
header('Content-Type: application/json');
echo json_encode($data);
?>