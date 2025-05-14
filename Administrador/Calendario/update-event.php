<?php
session_start();
include "db.php"; // Tu conexión PDO

header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["success" => false, "message" => "Error: Usuario no autenticado."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["id"]) || !isset($data["start"])) {
    echo json_encode(["success" => false, "message" => "Error: Datos incompletos (ID o fecha de inicio faltantes)."]);
    exit;
}

$id_evento = $data["id"];
$titulo = $data["title"] ?? null; // FullCalendar podría no enviar el título si solo se movió
$start_datetime_str = $data["start"];
// $end_datetime_str = $data["end"] ?? null; // Si manejas fecha de fin
$descripcion = $data["description"] ?? null; // Si se envía descripción
$color_evento = $data["backgroundColor"] ?? null; // Recibir el color

$current_user_id = $_SESSION['id_usuario'];

// Validar formato de color hexadecimal (opcional)
if ($color_evento && !preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $color_evento)) {
    $color_evento = null;
}

// Procesar fecha y hora de inicio
if (strpos($start_datetime_str, 'T') !== false) {
    list($fecha, $time_part) = explode("T", $start_datetime_str);
    $hora = substr($time_part, 0, 5) . ":00"; // Asegurar HH:MM:SS
} else {
    $fecha = $start_datetime_str;
    $hora = "00:00:00"; // Asumir evento de todo el día
}

try {
    // Construir la consulta dinámicamente basada en los campos proporcionados
    $sql_parts = [];
    $params = [];

    if ($titulo !== null) {
        $sql_parts[] = "tipo_evento = ?";
        $params[] = $titulo;
    }
    if ($descripcion !== null) {
        $sql_parts[] = "asignacion_evento = ?";
        $params[] = $descripcion;
    }
    if ($color_evento !== null) {
        $sql_parts[] = "color_evento = ?";
        $params[] = $color_evento;
    }
    
    // Siempre actualizamos fecha y hora de inicio
    $sql_parts[] = "fecha_evento = ?";
    $params[] = $fecha;
    $sql_parts[] = "hora_evento = ?";
    $params[] = $hora;

    if (empty($sql_parts)) {
        echo json_encode(["success" => true, "message" => "No hay campos para actualizar."]); // O false si se considera un error
        exit;
    }

    $sql = "UPDATE evento SET " . implode(", ", $sql_parts) . " WHERE id_evento = ? AND id_responsable = ?";
    $params[] = $id_evento;
    $params[] = $current_user_id; // Asegurar que el usuario solo actualice sus propios eventos

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Evento actualizado correctamente."]);
    } else {
        // Esto puede pasar si el evento no pertenece al usuario o el ID no existe, o no hubo cambios reales.
        echo json_encode(["success" => false, "message" => "No se actualizó el evento (puede que no pertenezca al usuario, no exista o no haya cambios)."]);
    }

} catch (PDOException $e) {
    error_log("Error al actualizar evento: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error al actualizar el evento en la base de datos: " . $e->getMessage()]);
}
?>