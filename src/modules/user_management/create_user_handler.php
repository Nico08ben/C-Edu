<?php
// No necesitas session_start() aquí si public/admin_user_management.php ya lo hace.
// Pero si lo dejas, asegúrate que esté al principio y con la comprobación:
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }

require_once(__DIR__ . '/../../config/database.php'); // RUTA CORREGIDA




if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Considera guardar un mensaje en la sesión y redirigir
    $_SESSION['user_management_message'] = "Error de seguridad: Token CSRF inválido.";
    $_SESSION['user_management_message_type'] = "error";
    header('Location: admin_user_management.php'); // O a donde sea apropiado
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->set_charset("utf8mb4");

    // Validar campo materia, si es realmente obligatorio aquí (parece serlo)
    // if (!isset($_POST["id_materia"]) || empty($_POST['id_materia'])) { // Podrías querer que "sin materia" sea una opción válida (ej. valor 0 o NULL)
    //     $_SESSION['user_management_message'] = "El campo materia es requerido.";
    //     $_SESSION['user_management_message_type'] = "error";
    //     header('Location: admin_user_management.php?action=create_form'); // Volver al formulario
    //     exit();
    // }

    $nombre = $conn->real_escape_string($_POST["nombre_usuario"]);
    $email = $conn->real_escape_string($_POST["email_usuario"]);
    $contraseña = password_hash($_POST["contraseña_usuario"], PASSWORD_DEFAULT);
    $telefono = isset($_POST["telefono_usuario"]) ? $conn->real_escape_string($_POST["telefono_usuario"]) : '';
    $institucion = (int) $_POST["id_institucion"];
    $rol = (int) $_POST["id_rol"];
    
    // Manejo de id_materia: Si no se envía o está vacío, y tu BD permite NULL para id_materia
    $materia = (!empty($_POST["id_materia"])) ? (int)$_POST["id_materia"] : null;


    // Ajusta la consulta SQL si id_materia puede ser NULL
    if ($materia === null) {
        $sql = "INSERT INTO usuario (email_usuario, contraseña_usuario, nombre_usuario, telefono_usuario, id_institucion, id_rol, id_materia) 
            VALUES (?, ?, ?, ?, ?, ?, NULL)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssii', $email, $contraseña, $nombre, $telefono, $institucion, $rol);
    } else {
        $sql = "INSERT INTO usuario (email_usuario, contraseña_usuario, nombre_usuario, telefono_usuario, id_institucion, id_rol, id_materia) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssiii', $email, $contraseña, $nombre, $telefono, $institucion, $rol, $materia);
    }


    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        $_SESSION['user_management_message'] = "Usuario registrado correctamente. ID: " . $userId;
        $_SESSION['user_management_message_type'] = "success";
    } else {
        $_SESSION['user_management_message'] = "Error al registrar usuario: " . $stmt->error;
        $_SESSION['user_management_message_type'] = "error";
    }

    $stmt->close();
} else {
    // Si no es POST, redirigir o mostrar error
    $_SESSION['user_management_message'] = "Acceso no válido al manejador.";
    $_SESSION['user_management_message_type'] = "error";
}

$conn->close();
header('Location: admin_user_management.php'); // Redirige a la lista de usuarios (o donde quieras que vaya)
exit();
?>