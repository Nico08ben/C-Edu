<?php
session_start();
include "db.php";
header('Content-Type: application/json');
$eventos = [];

// Asegúrate de que $conn esté inicializado correctamente
if (!isset($conn) || !($conn instanceof PDO)) {
    try {
        // Ajusta los valores de DSN, usuario y contraseña según tu configuración
        $conn = new PDO('mysql:host=localhost;dbname=nombre_de_tu_base_de_datos', 'usuario', 'contraseña');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        echo json_encode($eventos);
        exit;
    }
}

// Ensure $conn is initialized and user is authenticated...
// (Tu código existente para la conexión y sesión va aquí)
// ...

if (!isset($_SESSION['id_usuario'])) {
    echo json_encode($eventos);
    exit;
}
$current_user_id = $_SESSION['id_usuario'];

try {
    // 1. AÑADIMOS 'categoria_evento' A LA CONSULTA
    $stmt = $conn->prepare(
        "SELECT id_evento, titulo_evento, descripcion_evento, fecha_evento, hora_evento, fecha_fin_evento, hora_fin_evento, color_evento, categoria_evento
         FROM evento
         WHERE id_responsable = :current_user_id"
    );
    $stmt->bindParam(':current_user_id', $current_user_id, PDO::PARAM_INT);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $start_datetime = $row["fecha_evento"] . "T" . substr($row["hora_evento"], 0, 5);
        $end_datetime = null;
        if (!empty($row["fecha_fin_evento"]) && !empty($row["hora_fin_evento"])) {
            $end_datetime = $row["fecha_fin_evento"] . "T" . substr($row["hora_fin_evento"], 0, 5);
        }
        $eventos[] = [
            "id"              => $row["id_evento"],
            "title"           => $row["titulo_evento"],
            "description"     => $row["descripcion_evento"],
            "start"           => $start_datetime,
            "end"             => $end_datetime,
            "backgroundColor" => $row["color_evento"],
            "borderColor"     => $row["color_evento"],
            // 2. AÑADIMOS LA CATEGORÍA AL OBJETO DEL EVENTO
            "category"        => $row["categoria_evento"]
        ];
    }
} catch (PDOException $e) {
    error_log("Error en get-events.php: " . $e->getMessage());
}
echo json_encode($eventos);
?>