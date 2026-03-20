<?php
// Conexión a la base de datos
require_once "../config.php";

// Verificar sesión (opcional, pero recomendado por seguridad)
session_start();
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

// Array para almacenar todos los datos
$data = [];

// Contadores generales
$sql_usuarios = "SELECT COUNT(*) as total FROM usuarios";
$result = mysqli_query($conn, $sql_usuarios);
$row = mysqli_fetch_assoc($result);
$total_usuarios = $row['total'];

$sql_exitosos = "SELECT COUNT(*) as total FROM logs_acceso WHERE estado = 'exitoso'";
$result = mysqli_query($conn, $sql_exitosos);
$row = mysqli_fetch_assoc($result);
$accesos_exitosos = $row['total'];

$sql_fallidos = "SELECT COUNT(*) as total FROM logs_acceso WHERE estado = 'fallido'";
$result = mysqli_query($conn, $sql_fallidos);
$row = mysqli_fetch_assoc($result);
$accesos_fallidos = $row['total'];

$tasa_fallos = 0;
if ($accesos_exitosos + $accesos_fallidos > 0) {
    $tasa_fallos = round(($accesos_fallidos / ($accesos_exitosos + $accesos_fallidos)) * 100);
}

$data['contadores'] = [
    'total_usuarios' => $total_usuarios,
    'accesos_exitosos' => $accesos_exitosos,
    'accesos_fallidos' => $accesos_fallidos,
    'tasa_fallos' => $tasa_fallos
];

// Datos para la gráfica de accesos por día
$sql = "SELECT DATE(fecha_acceso) as dia, COUNT(*) as total, 
       SUM(CASE WHEN estado = 'exitoso' THEN 1 ELSE 0 END) as exitosos,
       SUM(CASE WHEN estado = 'fallido' THEN 1 ELSE 0 END) as fallidos
       FROM logs_acceso 
       WHERE fecha_acceso >= DATE_SUB(NOW(), INTERVAL 7 DAY)
       GROUP BY DATE(fecha_acceso)
       ORDER BY dia";

$result = mysqli_query($conn, $sql);

$labels = [];
$data_exitosos = [];
$data_fallidos = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = date("d/m", strtotime($row['dia']));
    $data_exitosos[] = $row['exitosos'];
    $data_fallidos[] = $row['fallidos'];
}

$data['accesos'] = [
    'labels' => $labels,
    'exitosos' => $data_exitosos,
    'fallidos' => $data_fallidos
];

// Datos para el pie chart
$data['pie'] = [
    'exitosos' => $accesos_exitosos,
    'fallidos' => $accesos_fallidos
];

// Devolver como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>