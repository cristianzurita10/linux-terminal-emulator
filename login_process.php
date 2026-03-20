<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require_once "config.php";
// Función para obtener la IP real del cliente
function getClientIP() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    // Si es una lista separada por comas (varios proxies), tomar solo la primera
    if (strpos($ipaddress, ',') !== false) {
        $ipaddress = explode(',', $ipaddress)[0];
    }
    
    return $ipaddress;
}

// Verificar si el formulario fue enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Verificar que los campos no estén vacíos
    if (empty(trim($_POST["username"])) || empty(trim($_POST["password"]))) {
        header("location: index.php?error=empty");
        exit;
    }
    
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $ip_address = getClientIP(); // Usamos la nueva función para obtener la IP real
    $estado = "fallido"; // Por defecto, asumimos que es fallido
    $usuario_id = null;
    
    // Preparar la consulta SELECT
    $sql = "SELECT id, username, password, es_admin FROM usuarios WHERE username = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        // Vincular variables a la sentencia preparada
        mysqli_stmt_bind_param($stmt, "s", $username);
        
        // Ejecutar la sentencia
        if (mysqli_stmt_execute($stmt)) {
            // Almacenar el resultado
            mysqli_stmt_store_result($stmt);
            
            // Verificar si el usuario existe
            if (mysqli_stmt_num_rows($stmt) == 1) {
                // Vincular las variables de resultado
                mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $es_admin);
                
                if (mysqli_stmt_fetch($stmt)) {
                    // Verificar la contraseña
                    if (password_verify($password, $hashed_password)) {
                        // La contraseña es correcta, iniciar sesión
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $username;
                        $_SESSION["es_admin"] = $es_admin;
                        // Actualizar el estado y el ID de usuario para el registro
                        $estado = "exitoso";
                        $usuario_id = $id;
                        // Registrar el acceso exitoso
                        $log_sql = "INSERT INTO logs_acceso (usuario_id, ip_address, estado, fecha_acceso) VALUES (?, ?, ?, NOW())";
                        if ($log_stmt = mysqli_prepare($conn, $log_sql)) {
                            mysqli_stmt_bind_param($log_stmt, "iss", $usuario_id, $ip_address, $estado);
                            mysqli_stmt_execute($log_stmt);
                            mysqli_stmt_close($log_stmt);
                        }
                        // Redirigir al usuario a la página correspondiente
                        if ($es_admin) {
                            header("location: admin/dashboard.php");
                        } else {
                            header("location: terminal.php");
                        }
                        exit;
                    } else {
                        // La contraseña no es válida
                        header("location: index.php?error=invalid");
                        exit;
                    }
                }
            } else {
                // No existe el usuario
                header("location: index.php?error=invalid");
                exit;
            }
        } else {
            header("location: index.php?error=system");
            exit;
        }
        // Registrar el intento fallido de acceso
        $log_sql = "INSERT INTO logs_acceso (usuario_id, ip_address, estado, fecha_acceso) VALUES (?, ?, ?, NOW())";
        if ($log_stmt = mysqli_prepare($conn, $log_sql)) {
            mysqli_stmt_bind_param($log_stmt, "iss", $usuario_id, $ip_address, $estado);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
        }
        // Cerrar la sentencia
        mysqli_stmt_close($stmt);
    }
    // Redirigir con mensaje de error
    header("location: index.php?error=invalid");
    exit;
}
// Si llegamos aquí, significa que no se envió un POST, redirigir al login
header("location: index.php");
exit;
?>