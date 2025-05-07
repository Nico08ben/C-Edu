<?php
// if (session_status() === PHP_SESSION_NONE) { session_start(); } // Ya iniciado por el script que lo incluye

require_once(__DIR__ . '/../../config/database.php');


$id_usuario_para_redirigir = $_POST['id_usuario'] ?? null;

// Verificar token CSRF
if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['user_management_message'] = "Token CSRF inválido.";
    $_SESSION['user_management_message_type'] = "error";
    if ($id_usuario_para_redirigir) {
        header('Location: admin_user_management.php?action=edit_form&id_usuario=' . $id_usuario_para_redirigir);
    } else {
        header('Location: admin_user_management.php');
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $required = ['id_usuario', 'new_password', 'confirm_password'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $_SESSION['user_management_message'] = "Todos los campos para cambiar la contraseña son requeridos.";
            $_SESSION['user_management_message_type'] = "error";
            if ($id_usuario_para_redirigir) {
                header('Location: admin_user_management.php?action=edit_form&id_usuario=' . $id_usuario_para_redirigir . '&sub_action=change_password_form'); // Quizás volver al modal/form de cambio de contraseña
            } else {
                header('Location: admin_user_management.php');
            }
            exit();
        }
    }
   
    if ($_POST['new_password'] !== $_POST['confirm_password']) {
        $_SESSION['user_management_message'] = "Las contraseñas no coinciden.";
        $_SESSION['user_management_message_type'] = "error";
        if ($id_usuario_para_redirigir) {
            header('Location: admin_user_management.php?action=edit_form&id_usuario=' . $id_usuario_para_redirigir . '&sub_action=change_password_form');
        } else {
            header('Location: admin_user_management.php');
        }
        exit();
    }
   
    $id = (int)$_POST['id_usuario'];
    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
   
    $stmt = $conn->prepare("UPDATE usuario SET contraseña_usuario = ? WHERE id_usuario = ?");
    if ($stmt) {
        $stmt->bind_param("si", $password, $id);
        if ($stmt->execute()) {
            $_SESSION['user_management_message'] = "Contraseña actualizada correctamente.";
            $_SESSION['user_management_message_type'] = "success";
        } else {
            $_SESSION['user_management_message'] = "Error al actualizar contraseña: " . $stmt->error;
            $_SESSION['user_management_message_type'] = "error";
        }
        $stmt->close();
    } else {
        $_SESSION['user_management_message'] = "Error al preparar la consulta para actualizar contraseña: " . $conn->error;
        $_SESSION['user_management_message_type'] = "error";
    }
} else {
    $_SESSION['user_management_message'] = "Método no permitido.";
    $_SESSION['user_management_message_type'] = "error";
}

$conn->close();

// Redirigir de vuelta al formulario de edición del usuario
if ($id_usuario_para_redirigir) {
    header('Location: admin_user_management.php?action=edit_form&id_usuario=' . $id_usuario_para_redirigir);
} else {
    // Si no hay ID de usuario (lo cual no debería pasar si la validación es correcta),
    // redirigir a la lista general.
    header('Location: admin_user_management.php');
}
exit();
?>