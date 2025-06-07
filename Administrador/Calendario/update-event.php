<?php
session_start();
include "db.php";
header('Content-Type: application/json');
$response = ["success" => false, "message" => "Error desconocido."];

if (!isset($_SESSION['id_usuario'])) {
    $response["message"] = "No autenticado.";
    echo json_encode($response);
    exit;
}

if ($conn === null) {
    $response["message"] = "Error de conexión a la base de datos.";
    error_log("update-event.php: La conexión a la BD es nula.");
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['id']) || empty($data['start'])) {
    $response["message"] = "Datos incompletos.";
    echo json_encode($response);
    exit;
}

// Recolectar todos los datos del evento
$id_evento = $data["id"];
$titulo = $data["title"] ?? null;
$descripcion = $data["description"] ?? null;
$start_datetime_str = $data["start"];
$end_datetime_str = $data["end"] ?? null;
$color_evento = $data["backgroundColor"] ?? '#3eb489';
$current_user_id = $_SESSION['id_usuario'];

// Procesar fecha/hora de inicio
list($fecha_inicio, $time_part_start) = explode("T", $start_datetime_str);
$hora_inicio = substr($time_part_start, 0, 8); // Formato HH:MM:SS

// Procesar fecha/hora de fin (puede ser null)
$fecha_fin = null;
$hora_fin = null;
if ($end_datetime_str) {
    list($fecha_fin, $time_part_end) = explode("T", $end_datetime_str);
    $hora_fin = substr($time_part_end, 0, 8); // Formato HH:MM:SS
}

try {
    $sql = "UPDATE evento SET 
                titulo_evento = :titulo,
                descripcion_evento = :descripcion,
                fecha_evento = :fecha_inicio, 
                hora_evento = :hora_inicio,
                fecha_fin_evento = :fecha_fin,
                hora_fin_evento = :hora_fin,
                color_evento = :color
            WHERE id_evento = :id AND id_responsable = :user_id";

    $stmt = $conn->prepare($sql);

    // --- ESTA ES LA MEJORA CLAVE ---
    // Verificar si la preparación de la consulta fue exitosa antes de ejecutarla.
    if ($stmt) {
        $stmt->execute([
            ':titulo' => $titulo,
            ':descripcion' => $descripcion,
            ':fecha_inicio' => $fecha_inicio,
            ':hora_inicio' => $hora_inicio,
            ':fecha_fin' => $fecha_fin,
            ':hora_fin' => $hora_fin,
            ':color' => $color_evento,
            ':id' => $id_evento,
            ':user_id' => $current_user_id
        ]);

        if ($stmt->rowCount() > 0) {
            $response["success"] = true;
            $response["message"] = "Evento actualizado correctamente.";
        } else {
            // No se afectaron filas, puede ser porque no hubo cambios o el evento no pertenece al usuario.
            $response["success"] = true; // Se considera éxito para no mostrar un error al usuario si no cambió nada.
            $response["message"] = "No se realizaron cambios.";
        }
    } else {
        // La preparación de la consulta falló.
        $errorInfo = $conn->errorInfo();
        error_log("Error de preparación de SQL en update-event.php: " . ($errorInfo[2] ?? 'Error desconocido.'));
        $response["message"] = "Error de base de datos: La consulta no se pudo preparar. Verifique la estructura de la tabla 'evento'.";
    }

} catch (PDOException $e) {
    error_log("Error en update-event.php: " . $e->getMessage());
    $response["message"] = "Excepción de base de datos al actualizar.";
}

echo json_encode($response);
?>