<?php
// logout.php - Cierra la sesión del usuario
session_start();
// Eliminar todas las variables de sesión
$_SESSION = array();
// Destruir la sesión
session_destroy();
// Redirigir a la página de inicio de sesión
header("location: index.php");
exit;
?>