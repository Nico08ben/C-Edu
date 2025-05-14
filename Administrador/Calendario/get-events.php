<?php
session_start();
include "db.php"; // Tu conexión PDO

header('Content-Type: application/json');
$eventos = [];

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([]); // Devolver array vacío si no está autenticado para evitar error en FullCalendar
    exit;
}

$current_user_id = $_SESSION['id_usuario'];

try {
    // Seleccionar la nueva columna color_evento
    $stmt = $conn->prepare(
        "SELECT id_evento, tipo_evento, asignacion_evento, fecha_evento, hora_evento, color_evento
         FROM evento
         WHERE id_responsable = :current_user_id"
    );
    $stmt->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hora_formateada = substr($row["hora_evento"], 0, 5); // Tomar HH:MM
        $start_datetime = $row["fecha_evento"] . "T" . $hora_formateada;

        $eventos[] = [
            "id"            => $row["id_evento"],
            "title"         => $row["tipo_evento"],
            "start"         => $start_datetime,
            "description"   => $row["asignacion_evento"],
            "backgroundColor" => $row["color_evento"], // Usar el color de la BD
            // "borderColor" => $row["color_evento"] // Opcional: para el borde
            // "allDay" => ($row["hora_evento"] === "00:00:00" || $row["hora_evento"] === "00:00") // Lógica simple para allDay
        ];
    }
    echo json_encode($eventos);

} catch (PDOException $e) {
    error_log("Error al obtener eventos: " . $e->getMessage());
    echo json_encode([]); // Devolver array vacío en caso de error
}
?>