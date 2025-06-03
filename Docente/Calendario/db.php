<?php
// nico08ben/c-edu/C-Edu-e0cbd86c63e8f85039c37dee1c1d89419cc5ebb6/Docente/Calendario/db.php
$host = "localhost";
$dbname = "cedu";
$username = "root";
$password = ""; // Si tu MySQL tiene contraseña, colócala aquí
$conn = null; // Inicializar $conn a null

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Si la conexión es exitosa, $conn será un objeto PDO.
} catch (PDOException $e) {
    // $conn permanecerá null.
    // Registrar el error en el servidor en lugar de usar die() con HTML.
    error_log("Error de Conexión PDO en db.php: " . $e->getMessage());
    // add-event.php (y otros scripts que incluyan este) deberán verificar si $conn es null.
}
?>