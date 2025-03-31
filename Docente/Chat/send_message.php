<?php
// Iniciar sesión para obtener el ID del usuario actual
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener el ID del usuario actual desde la sesión
$id_emisor = $_SESSION['id_usuario'];

// Incluir el archivo de conexión (ajusta la ruta según sea necesario)
require_once("../../conexion.php");

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['id_receptor']) || !isset($_POST['mensaje']) || empty($_POST['mensaje'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Obtener datos del formulario y sanitizarlos
$id_receptor = mysqli_real_escape_string($conexion, $_POST['id_receptor']);
$mensaje = mysqli_real_escape_string($conexion, $_POST['mensaje']);

// Convertir nombre a ID si es necesario (si estás usando nombres en lugar de IDs)
if (!is_numeric($id_receptor)) {
    // Buscar el ID basado en el nombre del usuario
    $sql = "SELECT id_usuario FROM usuario WHERE nombre_usuario = '$id_receptor'";
    $result = mysqli_query($conexion, $sql);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $id_receptor = $row['id_usuario'];
    } else {
        echo json_encode(['error' => 'Usuario receptor no encontrado', 'nombre' => $_POST['id_receptor']]);
        exit;
    }
}

// Insertar el mensaje en la base de datos
$sql = "INSERT INTO mensaje (id_emisor, id_receptor, mensaje) VALUES ($id_emisor, $id_receptor, '$mensaje')";
$result = mysqli_query($conexion, $sql);

if ($result) {
    // Obtener el ID del mensaje insertado
    $id_mensaje = mysqli_insert_id($conexion);
    
    // Recuperar el mensaje insertado para confirmación
    $sql_check = "SELECT * FROM mensaje WHERE id_mensaje = $id_mensaje";
    $result_check = mysqli_query($conexion, $sql_check);
    $mensaje_insertado = mysqli_fetch_assoc($result_check);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Mensaje enviado con éxito',
        'id_mensaje' => $id_mensaje,
        'mensaje_guardado' => $mensaje_insertado
    ]);
} else {
    echo json_encode([
        'error' => 'Error al enviar el mensaje: ' . mysqli_error($conexion),
        'query' => $sql
    ]);
}

// Cerrar la conexión
mysqli_close($conexion);
?>