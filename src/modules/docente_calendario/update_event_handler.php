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

if (empty($data['id']) || empty($data['title']) || empty($data['start'])) {
    echo json_encode(['success' => false, 'message' => 'ID, Título y fecha de inicio son requeridos']);
    exit;
}

$id = (int)$data['id'];
$titulo = $conn->real_escape_string($data['title']);
$descripcion = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
$color = isset($data['backgroundColor']) ? $conn->real_escape_string($data['backgroundColor']) : '#3eb489';

$startDateTime = new DateTime($data['start']);
$fecha_evento = $startDateTime->format('Y-m-d');
$hora_evento = $startDateTime->format('H:i:s');

// Manejo de fecha de fin
$fecha_fin_evento = null;
$hora_fin_evento = null;
if (!empty($data['end'])) {
    $endDateTime = new DateTime($data['end']);
    // $fecha_fin_evento = $endDateTime->format('Y-m-d'); 
    // $hora_fin_evento = $endDateTime->format('H:i:s');
    // Necesitarías campos en tu BD como fecha_fin_evento, hora_fin_evento
    // O podrías estar actualizando un campo de duración o un timestamp de finalización.
    // Por simplicidad, si actualizas solo inicio, título, desc:
}


// Asumiendo que solo se actualizan estos campos. Adapta si tienes más (ej. fecha_fin).
// Asegúrate de que el id_responsable coincide o que el usuario tiene permiso para editar.
$id_responsable_actual = $_SESSION['id_usuario'];

// Ejemplo SQL:
$sql = "UPDATE evento SET fecha_evento = ?, hora_evento = ?, tipo_evento = ?, asignacion_evento = ?, color_evento = ? 
        WHERE id_evento = ? AND id_responsable = ?"; // Solo permitir editar eventos propios
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en preparación de consulta: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("sssssii", $fecha_evento, $hora_evento, $titulo, $descripcion, $color, $id, $id_responsable_actual);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true]);
    } else {
        // Podría ser que el evento no pertenezca al usuario o no exista
        echo json_encode(['success' => false, 'message' => 'No se actualizó el evento. Verifique los datos o permisos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar evento: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>