<?php
// api/marcar_todas_leidas.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php'; // $conn estará disponible aquí

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
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

$idUsuario = (int)$_SESSION['id_usuario'];

$sql = "UPDATE notificacion SET estado_notificacion = 'leída'
        WHERE id_usuario = ? AND estado_notificacion = 'no leída'";

$stmt = $conn->prepare($sql);
if($stmt === false) {
    error_log("Error al preparar la consulta para marcar todas leídas (usuario $idUsuario): " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor.']);
    $conn->close();
    exit;
}

$stmt->bind_param('i', $idUsuario);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Todas las notificaciones marcadas como leídas.', 'filas_afectadas' => $stmt->affected_rows]);
} else {
    error_log("Error al ejecutar marcar todas las notificaciones como leídas para usuario $idUsuario: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar las notificaciones.']);
}
$stmt->close();
$conn->close();
?>