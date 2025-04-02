<?php
// upload_image.php - Script para subir imágenes de perfil

session_start();

// Verificar si se ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    // Para propósitos de prueba, podemos usar un ID fijo
    $_SESSION['id_usuario'] = 6; // Ajusta según sea necesario
    // O descomentar la siguiente línea para producción
    // echo json_encode(['success' => false, 'message' => 'No hay sesión iniciada']);
    // exit;
}

// Incluir archivo de conexión a la base de datos
// Corregir la ruta del archivo de conexión
$connection_file = "../../C-EDU/conexion.php";
if (file_exists($connection_file)) {
    include $connection_file;
} else {
    // Fallback a la ruta alternativa
    include "../../config/conexion.php";
}

$userId = $_SESSION['id_usuario'];

// Verificar que se ha subido una imagen
if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] > 0) {
    echo json_encode(['success' => false, 'message' => 'Error al subir el archivo: ' . $_FILES['profile_image']['error']]);
    exit;
}

// Verificar que es una imagen
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES['profile_image']['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Use: JPG, PNG, GIF o WEBP']);
    exit;
}

// Verificar tamaño (máximo 2MB)
if ($_FILES['profile_image']['size'] > 2097152) {
    echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 2MB']);
    exit;
}

// Leer la imagen
$imageData = file_get_contents($_FILES['profile_image']['tmp_name']);
$imageType = $_FILES['profile_image']['type'];

// Verificar si la conexión existe
if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Preparar consulta para actualizar la imagen
$query = "UPDATE usuario SET foto_perfil = ?, foto_tipo = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $imageData, $imageType, $userId);

// Ejecutar la consulta
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Imagen actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar imagen: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>