<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    exit("No autorizado");
}

$id_usuario = $_SESSION['id_usuario'];
$id_contacto = $_GET['id_contacto'] ?? null;

if ($id_contacto) {
    // Seleccionar los mensajes entre el usuario y el contacto
    $stmt = $pdo->prepare("SELECT * FROM mensaje WHERE 
      (id_emisor = ? AND id_receptor = ?) OR (id_emisor = ? AND id_receptor = ?)
      ORDER BY fecha_mensaje ASC");
    $stmt->execute([$id_usuario, $id_contacto, $id_contacto, $id_usuario]);
    $mensajes = $stmt->fetchAll();
    header('Content-Type: application/json');
    echo json_encode($mensajes);
} else {
    http_response_code(400);
    echo "Falta id_contacto";
}
?>
