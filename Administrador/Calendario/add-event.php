<?php
session_start();
include "db.php"; // Tu conexión PDO

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "message" => "Error: Usuario no autenticado."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["title"]) || !isset($data["start"])) {
    echo json_encode(["success" => false, "message" => "Error: Datos incompletos (título o inicio faltantes)."]);
    exit;
}

$titulo = trim($data["title"]);
$start_datetime_str = $data["start"];
$end_datetime_str = $data["end"] ?? null; // Puede ser null
$descripcion = $data["description"] ?? "";
$color_evento = $data["backgroundColor"] ?? null; // Recibir el color

if (empty($titulo)) {
    echo json_encode(["success" => false, "message" => "Error: El título del evento no puede estar vacío."]);
    exit;
}

// Validar formato de color hexadecimal (opcional pero recomendado)
if ($color_evento && !preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color_evento)) {
    $color_evento = null; // Si no es válido, se podría asignar un color por defecto o null
}

$fecha_evento_inicio = null;
$hora_evento_inicio = "00:00:00"; // Default para eventos de todo el día
$fecha_evento_fin = null; // Para eventos con duración específica
$hora_evento_fin = null;  // Para eventos con duración específica

// Procesar fecha y hora de inicio
if (strpos($start_datetime_str, 'T') !== false) {
    list($fecha_evento_inicio, $time_part_inicio) = explode("T", $start_datetime_str);
    $hora_evento_inicio = substr($time_part_inicio, 0, 5) . ":00"; // Asegurar HH:MM:SS
} else {
    $fecha_evento_inicio = $start_datetime_str; // Evento de todo el día
}

// Procesar fecha y hora de fin (si existe)
// Nota: FullCalendar y la tabla 'evento' manejan esto de forma diferente.
// La tabla 'evento' tiene solo fecha_evento y hora_evento (para el inicio).
// Si necesitas guardar la duración o fecha/hora de fin, deberías añadir columnas
// como `fecha_fin_evento` y `hora_fin_evento` a tu tabla.
// Por ahora, solo guardaremos el inicio y el color.

$categoria = "Otro"; // Puedes hacerlo dinámico si lo necesitas
$id_responsable = $_SESSION['id_usuario'];
$enlace = null;

try {
    // Añadir color_evento a la consulta INSERT
    $stmt = $conn->prepare(
        "INSERT INTO evento (fecha_evento, hora_evento, tipo_evento, asignacion_evento, categoria_evento, id_responsable, enlace_recurso, color_evento)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$fecha_evento_inicio, $hora_evento_inicio, $titulo, $descripcion, $categoria, $id_responsable, $enlace, $color_evento]);
    $newEventId = $conn->lastInsertId();

    echo json_encode([
        "success" => true,
        "id" => $newEventId,
        "message" => "Evento guardado correctamente.",
        // Devolver los datos del evento para que FullCalendar pueda usar el ID y color correctos
        "event" => [
            "id" => $newEventId,
            "title" => $titulo,
            "start" => $start_datetime_str,
            "end" => $end_datetime_str,
            "description" => $descripcion,
            "backgroundColor" => $color_evento,
            "id_responsable" => $id_responsable
        ]
    ]);

} catch (PDOException $e) {
    error_log("Error al guardar evento: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error al guardar el evento en la base de datos."]);
}
?>