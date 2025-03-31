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

// Incluir el archivo de conexión (ajusta la ruta según sea necesario)
require_once("../../conexion.php");

// Verificar que se reciba el ID del contacto
if (!isset($_GET['id_contacto'])) {
    echo json_encode(['error' => 'ID de contacto no especificado']);
    exit;
}

// Obtener y sanitizar el ID del contacto
$id_contacto = mysqli_real_escape_string($conexion, $_GET['id_contacto']);

// Convertir nombre a ID si es necesario (si estás usando nombres en lugar de IDs)
if (!is_numeric($id_contacto)) {
    // Buscar el ID basado en el nombre del usuario
    $sql = "SELECT id_usuario FROM usuario WHERE nombre_usuario = '$id_contacto'";
    $result = mysqli_query($conexion, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $id_contacto = $row['id_usuario'];
    } else {
        echo json_encode(['error' => 'Usuario no encontrado']);
        exit;
    }
}

// Consultar los mensajes entre el usuario actual y el contacto
$sql = "SELECT * FROM mensaje 
        WHERE (id_emisor = $id_usuario AND id_receptor = $id_contacto) 
        OR (id_emisor = $id_contacto AND id_receptor = $id_usuario) 
        ORDER BY fecha_mensaje ASC";

$result = mysqli_query($conexion, $sql);

if (!$result) {
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conexion)]);
    exit;
}

// Crear array para almacenar los mensajes
$mensajes = [];
while ($row = mysqli_fetch_assoc($result)) {
    $mensajes[] = $row;
}

// Agregar información de depuración
$debug = [
    'sql' => $sql,
    'id_usuario' => $id_usuario,
    'id_contacto' => $id_contacto,
    'count' => count($mensajes)
];

// Retornar los mensajes en formato JSON
echo json_encode($mensajes);

// Cerrar la conexión
mysqli_close($conexion);
?>