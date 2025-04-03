<?php
include '../../conexion.php'; // Conexión a la base de datos

session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Token CSRF inválido");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Establecer charset para manejar caracteres especiales
    $conn->set_charset("utf8mb4");
    
    // Obtener y sanitizar datos del formulario
    $nombre = $conn->real_escape_string($_POST["nombre_usuario"]);
    $email = $conn->real_escape_string($_POST["email_usuario"]);
    $contraseña = password_hash($_POST["contraseña_usuario"], PASSWORD_DEFAULT); // Encriptar la contraseña
    $telefono = isset($_POST["telefono_usuario"]) ? $conn->real_escape_string($_POST["telefono_usuario"]) : '';
    $institucion = (int)$_POST["id_institucion"];
    $rol = (int)$_POST["id_rol"];
    $materia = (int)$_POST["materia_usuario"]; // ID de la materia seleccionada

    // Insertar usuario en la base de datos
    $sql = "INSERT INTO usuario (email_usuario, contraseña_usuario, nombre_usuario, telefono_usuario, id_institucion, id_rol, id_materia) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiis", $email, $contraseña, $nombre, $telefono, $institucion, $rol, $materia);
    
    if ($stmt->execute()) {
        $userId = $stmt->insert_id;
        echo "Usuario registrado correctamente. id: $userId";
    } else {
        echo "Error al registrar usuario: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>