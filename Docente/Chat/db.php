<?php
$host = "localhost";
$user = "root"; // Cambiar si es necesario
$pass = ""; // Cambiar si es necesario
$dbname = "cedu"; // Asegúrate de que el nombre coincide

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>