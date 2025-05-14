<?php
$host = "localhost";
$dbname = "cedu";
$username = "root";
$password = ""; // Cambia esto si tu contraseña de MySQL no está vacía

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>