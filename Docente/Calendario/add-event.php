<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json'); // Asegurar encabezado de respuesta JSON
$response = ["success" => false, "message" => "Error desconocido al procesar el evento."]; // Respuesta por defecto

// Intentar incluir db.php y establecer la conexión
include "db.php"; // Define $conn (PDO) o lo deja como null si falla la conexión en db.php

// Verificar si la conexión se estableció correctamente
if ($conn === null) {
    $response["message"] = "Error crítico: No se pudo establecer la conexión con la base de datos. Revise los logs del servidor.";
    // Registrar este error también en el servidor si es necesario
    error_log("add-event.php: \$conn es null después de incluir db.php.");
    echo json_encode($response);
    exit;
}

if (!isset($_SESSION['id_usuario'])) {
    $response["message"] = "Error: Usuario no autenticado. No se puede asignar un responsable al evento.";
    echo json_encode($response);
    exit;
}
$id_responsable_session = $_SESSION['id_usuario'];

$input_data_raw = file_get_contents("php://input");
if ($input_data_raw === false) {
    $response["message"] = "Error: No se pudo leer el cuerpo de la solicitud (php://input).";
    echo json_encode($response);
    exit;
}
$data = json_decode($input_data_raw, true);

if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    $response["message"] = "Error: Los datos recibidos no tienen un formato JSON válido. Error JSON: " . json_last_error_msg();
    // Para depuración, podrías incluir $input_data_raw (sanitizado) en la respuesta
    // $response["received_data_raw"] = htmlspecialchars(mb_convert_encoding($input_data_raw, 'UTF-8', 'UTF-8'));
    echo json_encode($response);
    exit;
}
if ($data === null && empty($input_data_raw)) { // Si el cuerpo estaba vacío
    $response["message"] = "Error: No se recibieron datos en el cuerpo de la solicitud.";
    echo json_encode($response);
    exit;
}


$start_datetime = $data["start"] ?? null;
$titulo = $data["title"] ?? null;
$descripcion = $data["description"] ?? '';
$categoria_evento = $data["categoria_evento"] ?? "Otro";
$enlace_recurso = $data["enlace_recurso"] ?? null;

if (empty($start_datetime) || empty($titulo)) {
    $response["message"] = "Error: Los campos 'start' (fecha y hora de inicio) y 'title' (título) son requeridos.";
    echo json_encode($response);
    exit;
}

$datetime_parts = explode("T", $start_datetime);
if (count($datetime_parts) !== 2) {
    $response["message"] = "Error: El formato de 'start' (fecha y hora de inicio) no es válido. Debe ser YYYY-MM-DDTHH:MM.";
    echo json_encode($response);
    exit;
}
$fecha = $datetime_parts[0];
$hora = $datetime_parts[1];

if (!DateTime::createFromFormat('Y-m-d', $fecha) || !(DateTime::createFromFormat('H:i', $hora) || DateTime::createFromFormat('H:i:s', $hora))) {
     $response["message"] = "Error: Formato de fecha u hora inválido. Use YYYY-MM-DD para fecha y HH:MM o HH:MM:SS para hora.";
     $response["debug_fecha_recibida"] = $fecha;
     $response["debug_hora_recibida"] = $hora;
     echo json_encode($response);
     exit;
}

// Asegurar que la hora esté en formato HH:MM:SS para la base de datos si vino como HH:MM
if (preg_match('/^\d{2}:\d{2}$/', $hora)) { // Si es HH:MM
    $hora .= ":00"; // Añadir segundos
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO evento (fecha_evento, hora_evento, tipo_evento, asignacion_evento, categoria_evento, id_responsable, enlace_recurso)
         VALUES (:fecha, :hora, :titulo, :descripcion, :categoria, :id_responsable, :enlace)"
    );

    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':hora', $hora);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':categoria', $categoria_evento);
    $stmt->bindParam(':id_responsable', $id_responsable_session);
    $stmt->bindParam(':enlace', $enlace_recurso);

    if ($stmt->execute()) {
        $response["success"] = true;
        $response["id"] = $conn->lastInsertId();
        $response["message"] = "Evento añadido correctamente.";
    } else {
        $errorInfo = $stmt->errorInfo();
        $response["message"] = "Error al ejecutar la inserción en la base de datos.";
        // Loguear el error detallado en el servidor
        error_log("Error DB en add-event.php (execute): SQLSTATE[{$errorInfo[0]}] Code[{$errorInfo[1]}] Message[{$errorInfo[2]}]");
        $response["db_error_code"] = $errorInfo[1] ?? null; // Enviar código de error SQL para depuración (opcional)
    }
} catch (PDOException $e) {
    $response["message"] = "Excepción de base de datos al añadir evento (PDOException). Código: " . $e->getCode();
    error_log("PDOException in add-event.php: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
    $response["db_exception_code"] = $e->getCode();
} catch (Exception $e) {
    $response["message"] = "Error general al procesar la solicitud (Exception).";
    error_log("Exception in add-event.php: " . $e->getMessage());
}

echo json_encode($response);
?>