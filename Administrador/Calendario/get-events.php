<?php
session_start(); // FUNDAMENTAL: Iniciar sesión para acceder a $_SESSION['id_usuario']
include "db.php"; // Tu conexión a la base de datos (PDO en este ejemplo)

header('Content-Type: application/json');
$eventos = []; // Inicializar como array vacío

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    // Si no está logueado, no debería poder ver eventos. Devolver un array vacío o un error.
    echo json_encode($eventos); // Devolver array vacío para que FullCalendar no falle
    exit;
}

$current_user_id = $_SESSION['id_usuario'];

try {
    // Consulta SQL MODIFICADA para filtrar por id_responsable
    // También se incluye color_evento si ya lo añadiste a tu tabla
    $stmt = $conn->prepare(
        "SELECT id_evento, tipo_evento, asignacion_evento, fecha_evento, hora_evento, color_evento
         FROM evento
         WHERE id_responsable = :current_user_id" // <<<--- ESTA ES LA LÍNEA CLAVE
    );
    $stmt->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $hora_formateada = substr($row["hora_evento"], 0, 5); // Tomar solo HH:MM
        $start_datetime = $row["fecha_evento"] . "T" . $hora_formateada;

        $eventos[] = [
            "id"            => $row["id_evento"],
            "title"         => $row["tipo_evento"],
            "start"         => $start_datetime,
            "description"   => $row["asignacion_evento"],
            "backgroundColor" => $row["color_evento"], // Se usa el color de la BD
            // "allDay" => ($hora_formateada === "00:00") // Una lógica simple si tienes eventos de día completo
        ];
    }
    echo json_encode($eventos);

} catch (PDOException $e) {
    error_log("Error al obtener eventos para el usuario " . $current_user_id . ": " . $e->getMessage());
    echo json_encode($eventos); // Devolver array vacío en caso de error para no romper FullCalendar
}
?>