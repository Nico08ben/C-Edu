<?php
// delete_profile.php - Script para eliminar un usuario

// Ensure no output before our JSON response
error_reporting(0); // Disable error reporting for production
header('Content-Type: application/json'); // Set correct content type

session_start();

// Verificar si se ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No hay sesión iniciada']);
    exit;
}

// Incluir archivo de conexión a la base de datos
$connection_paths = [
    "../../C-EDU/conexion.php",
    "../../config/conexion.php",
    "../config/conexion.php",
    "conexion.php"
];

$conn = null;
foreach ($connection_paths as $path) {
    if (file_exists($path)) {
        include $path;
        if (isset($conn)) {
            break;
        }
    }
}

// Si aún no hay conexión, crear una
if (!isset($conn)) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "cedu";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos: ' . $conn->connect_error]);
        exit;
    }
}

$userId = $_SESSION['id_usuario'];

// En un entorno real, normalmente no eliminarías completamente el usuario, 
// sino que marcarías como inactivo o vaciarías sus datos personales

// Actualizar el usuario para eliminar sus datos personales
$query = "UPDATE usuario SET 
          nombre_usuario = 'Usuario Eliminado', 
          email_usuario = 'eliminado_" . $userId . "@example.com', 
          telefono_usuario = NULL, 
          foto_perfil = NULL, 
          foto_tipo = NULL 
          WHERE id_usuario = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);

// Ejecutar la consulta
if ($stmt->execute()) {
    // Destruir la sesión
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Cuenta eliminada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar cuenta: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>