<?php
// session_check.php - Archivo para verificar la sesión en cada página
session_start();
// Función para verificar si el usuario ha iniciado sesión
function verificarSesion() {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("location: index.php");
        exit;
    }
}
// Función para verificar si el usuario es administrador
function verificarAdmin() {
    verificarSesion();
    if (!isset($_SESSION["es_admin"]) || $_SESSION["es_admin"] !== 1) {
        header("location: terminal.php");
        exit;
    }
}
?>