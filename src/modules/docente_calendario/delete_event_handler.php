<?php
session_start();
require_once __DIR__ . '/../../../src/config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de evento requerido']);
    exit;
}

$id = (int)$data['id'];
$id_responsable_actual = $_SESSION['id_usuario'];

// Solo permitir eliminar eventos propios
$sql = "DELETE FROM evento WHERE id_evento = ? AND id_responsable = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en preparación de consulta: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("ii", $id, $id_responsable_actual);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se eliminó el evento. Puede que no exista o no tenga permisos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar evento: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>