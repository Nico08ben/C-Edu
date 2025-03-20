<?php
$servidor = "localhost"; // Servidor de MySQL (en XAMPP, es localhost)
$usuario = "root"; // Usuario de MySQL por defecto en XAMPP
$contraseña = ""; // Por defecto, XAMPP no tiene contraseña
$base_datos = "cedu"; // Nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servidor, $usuario, $contraseña, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
} else {
    // echo "Conexión exitosa"; // Puedes descomentar esto para probar la conexión
}
?>