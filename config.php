<?php
define("DB_SERVER", "localhost");
define("DB_USERNAME", "phpmyadmin");     //Usuario de la base de datos, cambia esto por tu usuario real
define("DB_PASSWORD", "QQUJYwAJmO6M");  // Contraseña de la base de datos, cambia esto por tu contraseña real
define("DB_NAME", "sistema_login");
// Conectar a la base de datos
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
// Verificar conexión
if(!$conn){
    die("ERROR: No se pudo conectar. " . mysqli_connect_error());
}
?>