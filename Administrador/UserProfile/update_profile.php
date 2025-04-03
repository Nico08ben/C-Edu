<?php
// update_profile.php - Script para actualizar información del usuario

// Ensure no output before our JSON response
error_reporting(0); // Disable error reporting for production
header('Content-Type: application/json'); // Set correct content type

session_start();

// Verificar si se ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    // Para propósitos de prueba, podemos usar un ID fijo
    $_SESSION['id_usuario'] = 6; // Ajusta según sea necesario
}

// Obtener y decodificar los datos JSON recibidos
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron datos válidos']);
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

// Preparar consulta para actualizar la información del usuario
$query = "UPDATE usuario SET nombre_usuario = ?, email_usuario = ?, telefono_usuario = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssi", $data['nombre'], $data['email'], $data['telefono'], $userId);

// Ejecutar la consulta
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar perfil: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>