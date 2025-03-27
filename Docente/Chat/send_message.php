<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(403);
    exit("No autorizado");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_emisor = $_SESSION['id_usuario'];
    $id_receptor = $_POST['id_receptor'] ?? null;
    $mensaje = trim($_POST['mensaje'] ?? '');
    
    if ($id_receptor && $mensaje) {
        $stmt = $pdo->prepare("INSERT INTO mensaje (id_emisor, id_receptor, mensaje) VALUES (?, ?, ?)");
        $stmt->execute([$id_emisor, $id_receptor, $mensaje]);
        echo "Mensaje enviado";
    } else {
        http_response_code(400);
        echo "Datos incompletos";
    }
} else {
    http_response_code(405);
    echo "Método no permitido";
}
?>
