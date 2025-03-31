<?php
// Iniciar sesión para obtener el ID del usuario actual
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener el ID del usuario actual desde la sesión
$id_usuario = $_SESSION['id_usuario'];

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "cedu");

// Verificar la conexión
if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conexion->connect_error]);
    exit;
}

// Verificar que se reciba el ID del contacto
if (!isset($_GET['id_contacto'])) {
    echo json_encode(['error' => 'ID de contacto no especificado']);
    exit;
}

// Obtener y sanitizar el ID del contacto
$id_contacto = $conexion->real_escape_string($_GET['id_contacto']);

// Convertir nombre a ID si es necesario (si estás usando nombres en lugar de IDs)
if (!is_numeric($id_contacto)) {
    // Buscar el ID basado en el nombre del usuario
    $sql = "SELECT id_usuario FROM usuario WHERE nombre_usuario = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $id_contacto);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $id_contacto = $row['id_usuario'];
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
    $stmt->close();
}

// Consultar los mensajes entre el usuario actual y el contacto
$sql = "SELECT * FROM mensaje 
        WHERE (id_emisor = ? AND id_receptor = ?) 
        OR (id_emisor = ? AND id_receptor = ?) 
        ORDER BY fecha_mensaje ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("iiii", $id_usuario, $id_contacto, $id_contacto, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();

// Crear array para almacenar los mensajes
$mensajes = [];
while ($row = $result->fetch_assoc()) {
    $mensajes[] = $row;
}

// Retornar los mensajes en formato JSON
echo json_encode($mensajes);

$stmt->close();
$conexion->close();
?>