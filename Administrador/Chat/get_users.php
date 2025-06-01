<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in or session expired.']);
    exit;
}

$pathToConexion = '../../conexion.php';
if (!file_exists($pathToConexion)) {
    echo json_encode(['success' => false, 'message' => 'Error critico: conexion.php no encontrado.']);
    exit;
}
include $pathToConexion;

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit;
}
$conn->set_charset("utf8");

$currentUserId = $_SESSION['id_usuario'];
$users = [];

$webRootPath = '/C-edu/';
// *** IMPORTANTE: Unifica esta ruta del avatar por defecto en TODA tu aplicación ***
$defaultAvatar = '/C-edu/uploads/profile_pictures/default-avatar.png';  // Ejemplo de ruta unificada

// Consulta SQL actualizada
$sql = "SELECT
            u.id_usuario,
            u.nombre_usuario AS fullName,
            u.foto_perfil_url,
            m_last.contenido_mensaje AS lastMessageContent,
            m_last.fecha_envio AS lastMessageDate,
            (
                SELECT COUNT(*)
                FROM mensaje unread_m
                WHERE unread_m.id_emisor = u.id_usuario -- Mensajes DEL usuario de la lista
                  AND unread_m.id_receptor = ?           -- PARA el usuario actual (logeado)
                  AND unread_m.leido = 0                 -- Y que no estén leídos
            ) AS unreadCount
        FROM
            usuario u
        LEFT JOIN
            mensaje m_last ON m_last.id_mensaje = (
                SELECT id_mensaje
                FROM mensaje sub_m
                WHERE
                    (sub_m.id_emisor = u.id_usuario AND sub_m.id_receptor = ?) OR -- Para el último mensaje
                    (sub_m.id_emisor = ? AND sub_m.id_receptor = u.id_usuario)    -- Para el último mensaje
                ORDER BY sub_m.fecha_envio DESC
                LIMIT 1
            )
        WHERE
            u.id_usuario != ? -- No incluir al usuario actual en la lista
        ORDER BY
            CASE WHEN m_last.fecha_envio IS NULL THEN 1 ELSE 0 END,
            m_last.fecha_envio DESC,
            u.nombre_usuario ASC";

$stmt = $conn->prepare($sql);

if ($stmt) {
    // Ahora son 4 parámetros para bind_param: uno para unreadCount, dos para last_message, uno para el WHERE final.
    $stmt->bind_param("iiii", $currentUserId, $currentUserId, $currentUserId, $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if (!empty($row['foto_perfil_url'])) {
            $row['foto_perfil_url_url'] = $webRootPath . htmlspecialchars($row['foto_perfil_url']);
        } else {
            $row['foto_perfil_url_url'] = $defaultAvatar;
        }
        unset($row['foto_perfil_url']);

        $row['lastMessageContent'] = $row['lastMessageContent'] ?? null;
        $row['lastMessageDate'] = $row['lastMessageDate'] ?? null;
        $row['unreadCount'] = $row['unreadCount'] ?? 0; // Asegurar que unreadCount siempre esté, por defecto 0
        
        $users[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'users' => $users]);
} else {
    error_log("Error en get_users.php al preparar SQL: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de usuarios: ' . $conn->error . ' (Query: ' . $sql . ')']);
}

$conn->close();
?>