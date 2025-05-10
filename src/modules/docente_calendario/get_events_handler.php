<?php
session_start();
require_once __DIR__ . '/../../../src/config/database.php'; // Ajusta la ruta a tu archivo principal de conexión mysqli

header('Content-Type: application/json');

// Verificar si el usuario está logueado y es un docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

$id_usuario_actual = $_SESSION['id_usuario'];

// Modifica la consulta para obtener solo los eventos del docente actual si es necesario
// Por ejemplo, si la tabla 'evento' tiene un campo 'id_usuario' o 'id_responsable'
// $sql = "SELECT id_evento, tipo_evento, fecha_evento, hora_evento, asignacion_evento, color_evento FROM evento WHERE id_responsable = ?";
// $stmt = $conn->prepare($sql);
// $stmt->bind_param("i", $id_usuario_actual);

// Si los eventos son generales para todos los docentes o no están ligados a un usuario específico en esta tabla:
$sql = "SELECT id_evento, tipo_evento, fecha_evento, hora_evento, asignacion_evento, color_evento FROM evento";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['error' => 'Error preparando la consulta: ' . $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();
$eventos = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $start = $row["fecha_evento"] . "T" . $row["hora_evento"];
        // Asegúrate de que el formato de hora_evento sea HH:MM:SS o compatible con ISO8601
        
        $eventos[] = [
            "id" => $row["id_evento"],
            "title" => $row["tipo_evento"],
            "start" => $start,
            "end" => null, // Añade lógica para 'end' si tienes fecha/hora de finalización
            "description" => $row["asignacion_evento"],
            "backgroundColor" => $row["color_evento"] ?? '#3eb489', // Color por defecto si no está en BD
            "borderColor" => $row["color_evento"] ?? '#3eb489'
            // "allDay" => false, // Añade lógica si manejas eventos de día completo
        ];
    }
    $result->free();
} else {
    echo json_encode(['error' => 'Error ejecutando la consulta: ' . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit;
}

$stmt->close();
$conn->close();

echo json_encode($eventos);
?>