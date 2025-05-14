<?php
// api/marcar_leida.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Método no permitido.']);
    $conn->close();
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado.']);
    $conn->close();
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$idNotificacion = isset($data['id_notificacion']) ? (int)$data['id_notificacion'] : null;
$idUsuario = (int)$_SESSION['id_usuario'];

if (!$idNotificacion) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de notificación no proporcionado.']);
    $conn->close();
    exit;
}

$sql = "UPDATE notificacion SET estado_notificacion = 'leída'
        WHERE id_notificacion = ? AND id_usuario = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error al preparar la consulta para marcar leída (notif $idNotificacion, usuario $idUsuario): " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor.']);
    $conn->close();
    exit;
}

$stmt->bind_param('ii', $idNotificacion, $idUsuario);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Notificación marcada como leída.']);
    } else {
        // No se actualizó ninguna fila, puede que la notificación no pertenezca al usuario o no exista, o ya estaba leída.
        // Para diferenciar, podrías hacer un SELECT previo, pero para este caso, es suficiente.
        echo json_encode(['success' => false, 'message' => 'Notificación no actualizada (puede que no exista, no pertenezca al usuario o ya estuviera leída).']);
    }
} else {
    error_log("Error al ejecutar marcar notificación como leída (notif $idNotificacion, usuario $idUsuario): " . $stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar la notificación.']);
}
$stmt->close();
$conn->close();
?>