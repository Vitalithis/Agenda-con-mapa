<?php
$host = "localhost"; // Cambia según tu configuración
$usuario = "root"; // Cambia según tu configuración
$contrasena = ""; // Cambia según tu configuración
$base_de_datos = "sodatal"; // Cambia según tu base de datos

$conexion = mysqli_connect($host, $usuario, $contrasena, $base_de_datos);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>