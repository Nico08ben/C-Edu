<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
header('Content-Type: application/json');
$response = ["success" => false, "message" => "Error desconocido."];
include "db.php";

if ($conn === null || !isset($_SESSION['id_usuario'])) {
    $response["message"] = "Error de autenticación o conexión.";
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (empty($data['start']) || empty($data['title'])) {
    $response["message"] = "Título y fecha de inicio son requeridos.";
    echo json_encode($response);
    exit;
}

$titulo = $data["title"];
$descripcion = $data["description"] ?? '';
$start_datetime = $data["start"];
$end_datetime = $data["end"] ?? null;
$categoria_evento = $data["categoria_evento"] ?? "Otro";
$color_evento = $data["color"] ?? '#3eb489';
$id_responsable = $_SESSION['id_usuario'];

list($fecha_inicio, $hora_inicio) = explode("T", $start_datetime);
$hora_inicio .= ":00";

$fecha_fin = null;
$hora_fin = null;
if (!empty($end_datetime)) {
    list($fecha_fin, $hora_fin) = explode("T", $end_datetime);
    $hora_fin .= ":00";
}

try {
    $stmt = $conn->prepare(
        "INSERT INTO evento (titulo_evento, descripcion_evento, fecha_evento, hora_evento, fecha_fin_evento, hora_fin_evento, categoria_evento, id_responsable, color_evento)
         VALUES (:titulo, :descripcion, :fecha_inicio, :hora_inicio, :fecha_fin, :hora_fin, :categoria, :id_responsable, :color)"
    );
    $stmt->execute([
        ':titulo' => $titulo,
        ':descripcion' => $descripcion,
        ':fecha_inicio' => $fecha_inicio,
        ':hora_inicio' => $hora_inicio,
        ':fecha_fin' => $fecha_fin,
        ':hora_fin' => $hora_fin,
        ':categoria' => $categoria_evento,
        ':id_responsable' => $id_responsable,
        ':color' => $color_evento
    ]);
    $response["success"] = true;
    $response["message"] = "Evento añadido.";
    $response["id"] = $conn->lastInsertId();
} catch (PDOException $e) {
    error_log("Error en add-event.php: " . $e->getMessage());
    $response["message"] = "Error de base de datos.";
}
echo json_encode($response);
?>