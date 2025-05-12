<?php
// api/obtener_notificaciones.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'conexion.php'; // $conn estará disponible aquí

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'Usuario no autenticado.']);
    $conn->close();
    exit;
}

$idUsuario = (int)$_SESSION['id_usuario'];
$soloNoLeidas = isset($_GET['estado']) && $_GET['estado'] === 'no_leida';
$limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 10;

$sql = "SELECT id_notificacion, tipo_notificacion, mensaje, enlace, fecha_notificacion, estado_notificacion
        FROM notificacion
        WHERE id_usuario = ?";

if ($soloNoLeidas) {
    $sql .= " AND estado_notificacion = 'no leída'";
}
$sql .= " ORDER BY fecha_notificacion DESC LIMIT ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Error al preparar la consulta para obtener notificaciones (usuario $idUsuario): " . $conn->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error interno del servidor.']);
    $conn->close();
    exit;
}

$stmt->bind_param('ii', $idUsuario, $limite);

$notificaciones = [];
if ($stmt->execute()) {
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notificaciones[] = $row;
    }
    $stmt->close();
} else {
    error_log("Error al ejecutar la obtención de notificaciones para usuario $idUsuario: " . $stmt->error);
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener notificaciones.']);
    $conn->close();
    exit;
}

// Contar todas las no leídas para el badge/contador
$totalNoLeidas = 0;
$sqlCount = "SELECT COUNT(*) as total_no_leidas FROM notificacion WHERE id_usuario = ? AND estado_notificacion = 'no leída'";
$stmtCount = $conn->prepare($sqlCount);
if($stmtCount) {
    $stmtCount->bind_param('i', $idUsuario);
    if($stmtCount->execute()){
        $resultCount = $stmtCount->get_result();
        $conteo = $resultCount->fetch_assoc();
        $totalNoLeidas = $conteo['total_no_leidas'] ?? 0;
    } else {
         error_log("Error al ejecutar conteo de notificaciones para usuario $idUsuario: " . $stmtCount->error);
    }
    $stmtCount->close();
} else {
    error_log("Error al preparar conteo de notificaciones para usuario $idUsuario: " . $conn->error);
}


echo json_encode(['notificaciones' => $notificaciones, 'total_no_leidas' => (int)$totalNoLeidas]);
$conn->close();
?>