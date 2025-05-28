<?php
ini_set('display_errors', 1); // Temporal para depuración
ini_set('display_startup_errors', 1); // Temporal para depuración
error_reporting(E_ALL); // Temporal para depuración

session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in or session expired.']);
    exit;
}

// Ruta a conexion.php: D:\xampp\htdocs\C-Edu\conexion.php
// Desde: D:\xampp\htdocs\C-Edu\Administrador\Chat\get_users.php
// Ruta correcta: ../../conexion.php
$pathToConexion = '../../conexion.php'; 
if (!file_exists($pathToConexion)) {
    echo json_encode(['success' => false, 'message' => 'Error critico: conexion.php no encontrado en la ruta esperada: ' . realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . $pathToConexion .'. Por favor, verifica la estructura de tus carpetas.']);
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

// Asumiendo que C-edu es accesible desde la raíz web (ej. http://localhost/C-edu/)
$profilePicBasePath = '/C-edu/uploads/'; 
// La ruta al avatar por defecto debe ser accesible desde el navegador:
// D:\xampp\htdocs\C-Edu\Administrador\Chat\assets\images\default-avatar.png
$defaultAvatar = '/C-edu/Administrador/Chat/assets/images/default-avatar.png'; 

// CORRECCIÓN: Eliminada la columna 'apellido' de la consulta.
$sql = "SELECT id_usuario, nombre_usuario, foto_perfil_url FROM usuario WHERE id_usuario != ?";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $currentUserId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        // CORRECCIÓN: fullName ahora solo usa nombre_usuario.
        $row['fullName'] = trim($row['nombre_usuario']); 
        if (!empty($row['foto_perfil_url'])) {
            $row['foto_perfil_url_url'] = $profilePicBasePath . htmlspecialchars($row['foto_perfil_url']);
        } else {
            $row['foto_perfil_url_url'] = $defaultAvatar;
        }
        unset($row['foto_perfil_url']); 
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