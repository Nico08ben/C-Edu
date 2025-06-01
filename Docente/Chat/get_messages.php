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
// Ruta base web donde se encuentra la carpeta raíz de tu aplicación.
$webRootPath = '/C-edu/'; 
// Ruta completa al avatar por defecto.
$defaultAvatar = '/C-edu/uploads/profile_pictures/default-avatar.png';

$sql = "
    SELECT m.id_mensaje, m.id_emisor, m.id_receptor, m.contenido_mensaje, m.fecha_envio, m.leido,
           u_emisor.nombre_usuario as emisor_nombre, 
           u_emisor.foto_perfil_url as emisor_foto_path 
    FROM mensaje m
    JOIN usuario u_emisor ON m.id_emisor = u_emisor.id_usuario
    WHERE ((m.id_emisor = ? AND m.id_receptor = ?) OR (m.id_emisor = ? AND m.id_receptor = ?))
    AND m.id_mensaje > ?
    ORDER BY m.fecha_envio ASC
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("iiiii", $currentUserId, $chatWithUserId, $chatWithUserId, $currentUserId, $lastMessageId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $row['emisor_fullName'] = trim($row['emisor_nombre']); 
        
        // Construcción de la URL de la foto del emisor
        if (!empty($row['emisor_foto_path'])) {
            // $row['emisor_foto_path'] viene de la BD como 'uploads/profile_pictures/nombre_archivo.png'
            // Se concatena con $webRootPath para formar la URL completa:
            // '/C-edu/uploads/profile_pictures/nombre_archivo.png'
            $row['emisor_foto_url'] = $webRootPath . htmlspecialchars($row['emisor_foto_path']);
        } else {
            $row['emisor_foto_url'] = $defaultAvatar; // Ya es una ruta completa
        }
        unset($row['emisor_foto_path']); // Elimina la ruta original de la BD del resultado
        
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