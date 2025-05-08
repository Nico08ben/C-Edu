<?php
include '../../conexion.php';
session_start();

// Verificar token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Token CSRF inválido");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar campos
    $required = ['id_usuario', 'new_password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            die("Todos los campos son requeridos");
        }
    }
   
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        die("Las contraseñas no coinciden");
    }
   
    // Actualizar contraseña
    $id = (int)$_POST['id_usuario'];
    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
   
    $stmt = $conn->prepare("UPDATE usuario SET contrasena_usuario = ? WHERE id_usuario = ?");
    $stmt->bind_param("si", $password, $id);
   
    if ($stmt->execute()) {
        echo "Contraseña actualizada correctamente";
    } else {
        echo "Error al actualizar contraseña: " . $stmt->error;
    }
   
    $stmt->close();
}

$conn->close();
?>