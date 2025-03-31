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

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "cedu");

// Verificar la conexión
if ($conexion->connect_error) {
    echo json_encode(['error' => 'Error de conexión: ' . $conexion->connect_error]);
    exit;
}

// Verificar que se recibieron los datos necesarios
if (!isset($_POST['id_receptor']) || !isset($_POST['mensaje']) || empty($_POST['mensaje'])) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

// Obtener datos del formulario y sanitizarlos
$id_receptor = $conexion->real_escape_string($_POST['id_receptor']);
$mensaje = $conexion->real_escape_string($_POST['mensaje']);

// Insertar el mensaje en la base de datos
$sql = "INSERT INTO mensaje (id_emisor, id_receptor, mensaje) VALUES (?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("iis", $id_emisor, $id_receptor, $mensaje);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Mensaje enviado con éxito']);
} else {
    echo json_encode(['error' => 'Error al enviar el mensaje: ' . $stmt->error]);
}

$stmt->close();
$conexion->close();
?>