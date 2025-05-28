<?php
ini_set('display_errors', 1); // Temporal para depuración
ini_set('display_startup_errors', 1); // Temporal para depuración
error_reporting(E_ALL); // Temporal para depuración

session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$pathToConexion = '../../conexion.php'; 
if (!file_exists($pathToConexion)) {
    echo json_encode(['success' => false, 'message' => 'Error critico: conexion.php no encontrado en ' . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $pathToConexion]);
    exit;
}
include $pathToConexion;

if ($conn->connect_error) { 
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit; 
}
$conn->set_charset("utf8");

header('Content-Type: application/json');

$currentUserId = $_SESSION['id_usuario'];
$chatWithUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$lastMessageId = isset($_GET['last_message_id']) ? intval($_GET['last_message_id']) : 0;

if (empty($chatWithUserId)) {
    echo json_encode(['success' => false, 'message' => 'Chat user ID not provided.']);
    exit;
}

$messages = [];
$profilePicBasePath = '/C-edu/uploads/'; 
$defaultAvatar = '/C-edu/Administrador/Chat/assets/images/default-avatar.png';

// Inside get_messages.php

// ... other code ...

// Make sure this is the SQL query you are using:
$sql = "
    SELECT m.id_mensaje, m.id_emisor, m.id_receptor, m.contenido_mensaje, m.fecha_envio, m.leido,
           u_emisor.nombre_usuario as emisor_nombre, 
           u_emisor.foto_perfil_url as emisor_foto_path  -- <<< THIS IS THE CRITICAL CHANGE
    FROM mensaje m
    JOIN usuario u_emisor ON m.id_emisor = u_emisor.id_usuario
    WHERE ((m.id_emisor = ? AND m.id_receptor = ?) OR (m.id_emisor = ? AND m.id_receptor = ?))
    AND m.id_mensaje > ?
    ORDER BY m.fecha_envio ASC
";

// The error occurs on this line (or the line where $conn->prepare is)
$stmt = $conn->prepare($sql); // This is line 51 or around it

// ... rest of the script ...

if ($stmt) {
    $stmt->bind_param("iiiii", $currentUserId, $chatWithUserId, $chatWithUserId, $currentUserId, $lastMessageId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // CORRECCIÓN: emisor_fullName ahora solo usa emisor_nombre.
        $row['emisor_fullName'] = trim($row['emisor_nombre']); 
        if (!empty($row['emisor_foto'])) {
            $row['emisor_foto_url'] = $profilePicBasePath . htmlspecialchars($row['emisor_foto']);
        } else {
            $row['emisor_foto_url'] = $defaultAvatar;
        }
        unset($row['emisor_foto']);
        $messages[] = $row;
    }
    $stmt->close();

    if (!empty($messages) || $lastMessageId == 0) {
        $updateReadSql = "UPDATE mensaje SET leido = 1 WHERE id_receptor = ? AND id_emisor = ? AND leido = 0";
        $updateStmt = $conn->prepare($updateReadSql);
        if ($updateStmt) {
            $updateStmt->bind_param("ii", $currentUserId, $chatWithUserId);
            $updateStmt->execute();
            $updateStmt->close();
        } else {
            error_log("Error al preparar la actualización de 'leido' en get_messages.php: " . $conn->error);
        }
    }
    
    echo json_encode(['success' => true, 'messages' => $messages, 'current_user_id' => $currentUserId]);
} else {
    error_log("Error en get_messages.php al preparar SQL: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de mensajes: ' . $conn->error . ' (Query: ' . $sql . ')']);
}

$conn->close();
?>