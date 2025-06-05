<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');
$response = ["success" => false, "message" => "Error desconocido al eliminar el evento."];

include "db.php"; // Defines $conn

// Check DB connection
if ($conn === null) {
    $response["message"] = "Error crítico: No se pudo conectar a la base de datos.";
    error_log("delete-event.php: \$conn is null after including db.php.");
    echo json_encode($response);
    exit;
}

// Check user authentication
if (!isset($_SESSION['id_usuario'])) {
    $response["message"] = "Error: Usuario no autenticado. No se puede eliminar el evento.";
    echo json_encode($response);
    exit;
}
$current_user_id = $_SESSION['id_usuario'];

$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!$data || !isset($data["id"])) {
    $response["message"] = "Error: ID de evento no proporcionado o datos inválidos.";
    echo json_encode($response);
    exit;
}
$id_evento = $data["id"];

try {
    // Prepare statement to delete event only if it belongs to the current user
    $stmt = $conn->prepare("DELETE FROM evento WHERE id_evento = :id_evento AND id_responsable = :id_responsable");
    $stmt->bindParam(':id_evento', $id_evento, PDO::PARAM_INT);
    $stmt->bindParam(':id_responsable', $current_user_id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        if ($stmt->rowCount() > 0) {
            $response["success"] = true;
            $response["message"] = "Evento eliminado correctamente.";
        } else {
            // No rows affected: event didn't exist or didn't belong to the user
            $response["message"] = "No se eliminó el evento. Puede que no exista o no tenga permiso para eliminarlo.";
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        $response["message"] = "Error al ejecutar la eliminación en la base de datos.";
        error_log("Error DB en delete-event.php (execute): SQLSTATE[{$errorInfo[0]}] Code[{$errorInfo[1]}] Message[{$errorInfo[2]}]");
    }
} catch (PDOException $e) {
    $response["message"] = "Excepción de base de datos al eliminar evento (PDOException).";
    error_log("PDOException in delete-event.php: " . $e->getMessage() . " (Code: " . $e->getCode() . ")");
} catch (Exception $e) {
    $response["message"] = "Error general al procesar la solicitud (Exception).";
    error_log("Exception in delete-event.php: " . $e->getMessage());
}

echo json_encode($response);
?>