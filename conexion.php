<?php
$servidor = "localhost";
$usuario = "root";
$contraseña = "";
$base_datos = "cedu";

// Crear conexión
$conn = new mysqli($servidor, $usuario, $contraseña, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
