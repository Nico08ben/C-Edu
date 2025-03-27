<?php
// db.php: Conexión a la base de datos cedu
$host = 'localhost';
$db   = 'cedu';
$user = 'tu_usuario';       // Cambia por tu usuario de la BD
$pass = 'tu_contraseña';     // Cambia por tu contraseña de la BD
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>