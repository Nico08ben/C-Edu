<?php
// /Administrador/Chat/get_user_details.php

// Mantenemos esto por si acaso, ¡recuerda quitarlo al final!
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['id_usuario'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

require_once '../../conexion.php';

header('Content-Type: application/json');
$conn->set_charset("utf8");

if ($conn->connect_error) { 
    echo json_encode(['success' => false, 'message' => 'Connection failed: ' . $conn->connect_error]);
    exit; 
}

$userIdToFetch = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($userIdToFetch <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid User ID provided.']);
    exit;
}

$webRootPath = '/C-edu/';
$defaultAvatar = $webRootPath . 'uploads/profile_pictures/default-avatar.png';

// --- LA CORRECCIÓN ESTÁ AQUÍ ---
$sql = "
    SELECT 
        u.nombre_usuario,
        u.email_usuario,
        u.telefono_usuario,
        u.foto_perfil_url,
        r.tipo_rol, -- CORREGIDO: Antes era r.nombre_rol
        m.nombre_materia
    FROM usuario u
    LEFT JOIN rol r ON u.id_rol = r.id_rol
    LEFT JOIN materia m ON u.id_materia = m.id_materia
    WHERE u.id_usuario = ?
";
// --- FIN DE LA CORRECCIÓN ---

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $userIdToFetch);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (!empty($user['foto_perfil_url'])) {
            $user['foto_perfil_url_full'] = $webRootPath . htmlspecialchars($user['foto_perfil_url']);
        } else {
            $user['foto_perfil_url_full'] = $defaultAvatar;
        }
        
        $user['nombre_materia'] = $user['nombre_materia'] ?? 'No asignada';
        // Renombramos la clave en el array de respuesta para mayor claridad en el JS
        $user['nombre_rol'] = $user['tipo_rol'];
        unset($user['foto_perfil_url'], $user['tipo_rol']);

        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }

    $stmt->close();
} else {
    error_log("Error en get_user_details.php al preparar SQL: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta. Detalles: ' . $conn->error]);
}

$conn->close();
?>