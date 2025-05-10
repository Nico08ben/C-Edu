<?php
session_start();
require_once __DIR__ . '/../../../src/config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar si el usuario está logueado y es un docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['title']) || empty($data['start'])) {
    echo json_encode(['success' => false, 'message' => 'Título y fecha de inicio son requeridos']);
    exit;
}

$titulo = $conn->real_escape_string($data['title']);
$descripcion = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
$color = isset($data['backgroundColor']) ? $conn->real_escape_string($data['backgroundColor']) : '#3eb489'; // Color por defecto

// FullCalendar envía fechas en formato ISO8601 (ej: "2024-05-10T10:00:00.000Z")
// Necesitamos separar fecha y hora para la base de datos
$startDateTime = new DateTime($data['start']);
$fecha_evento = $startDateTime->format('Y-m-d');
$hora_evento = $startDateTime->format('H:i:s');

// Manejo de fecha de fin (opcional)
$fecha_fin_evento = null;
$hora_fin_evento = null;
if (!empty($data['end'])) {
    $endDateTime = new DateTime($data['end']);
    $fecha_fin_evento = $endDateTime->format('Y-m-d'); // Asumiendo que tienes campos para fecha_fin y hora_fin
    $hora_fin_evento = $endDateTime->format('H:i:s');   // o un solo campo datetime_fin
}


$id_responsable = $_SESSION['id_usuario']; // Asignar el docente actual como responsable
$categoria = "Otro"; // Puedes hacerlo más dinámico si es necesario
$enlace = null; // Puedes hacerlo más dinámico

// Ajusta tu tabla 'evento'. Si no tienes fecha_fin_evento y hora_fin_evento, omítelos.
// Lo mismo para 'color_evento'.
// Ejemplo de SQL (ajusta a tu tabla):
$sql = "INSERT INTO evento (fecha_evento, hora_evento, tipo_evento, asignacion_evento, categoria_evento, id_responsable, enlace_recurso, color_evento) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en preparación de consulta: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("sssssiss", $fecha_evento, $hora_evento, $titulo, $descripcion, $categoria, $id_responsable, $enlace, $color);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id' => $conn->insert_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al añadir evento: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>