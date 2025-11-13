<?php
$host = "localhost";
$user = "root";
$pass = "";
$db = "Xhess";

$conexion = new mysqli(hostname: $host, username: $user, password: $pass, database: $db);
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>