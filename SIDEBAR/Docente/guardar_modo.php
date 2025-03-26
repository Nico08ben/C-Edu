<?php
session_start();
include("conexion.php"); // Asegúrate de incluir tu conexión a la BD

if (!isset($_SESSION['id_usuario'])) {
    echo "Error: Usuario no autenticado.";
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$modo_tema = $_POST['modo']; // 'claro' u 'oscuro'

// Actualizar la preferencia en la base de datos
$sql = "UPDATE usuario SET modo_tema = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $modo_tema, $id_usuario);

if ($stmt->execute()) {
    echo "Modo guardado correctamente.";
} else {
    echo "Error al guardar el modo.";
}

$stmt->close();
$conn->close();
?>