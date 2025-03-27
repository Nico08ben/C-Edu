<?php
include '../../conexion.php'; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre_usuario"];
    $email = $_POST["email_usuario"];
    $contraseña = password_hash($_POST["contraseña_usuario"], PASSWORD_DEFAULT); // Encriptar la contraseña
    $telefono = $_POST["telefono_usuario"];
    $institucion = $_POST["id_institucion"];
    $rol = $_POST["id_rol"];

    // Insertar usuario en la base de datos
    $sql = "INSERT INTO usuario (email_usuario, contraseña_usuario, nombre_usuario, telefono_usuario, id_institucion, id_rol) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $email, $contraseña, $nombre, $telefono, $institucion, $rol);
    
    if ($stmt->execute()) {
        echo "<script>alert('Usuario registrado correctamente'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Error al registrar usuario'); window.location.href='registro.php';</script>";
    }

    $stmt->close();
}

$conn->close();
?>
