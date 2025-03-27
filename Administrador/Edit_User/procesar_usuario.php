<?php
$servidor = "localhost";
$usuario = "root";
$contraseña = "";
$base_datos = "cedu";

// Conectar a la base de datos
$conn = new mysqli($servidor, $usuario, $contraseña, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si los datos han sido enviados por POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre_usuario"];
    $email = $_POST["email_usuario"];
    $contraseña = password_hash($_POST["contraseña_usuario"], PASSWORD_DEFAULT); // Encriptar la contraseña
    $telefono = $_POST["telefono_usuario"];
    $institucion = $_POST["id_institucion"];
    $rol = $_POST["id_rol"];

    // Preparar la consulta SQL con el nombre correcto de la tabla y las columnas
    $sql = "INSERT INTO usuario (email_usuario, contraseña_usuario, nombre_usuario, telefono_usuario, id_institucion, id_rol) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssii", $email, $contraseña, $nombre, $telefono, $institucion, $rol);
    
    if ($stmt->execute()) {
        echo "Usuario creado exitosamente";
    } else {
        echo "Error al crear el usuario: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
