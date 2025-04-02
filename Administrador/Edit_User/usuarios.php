<?php
$servidor = "localhost";
$usuario = "root";
$contraseña = "";
$base_datos = "cedu";

// Crear conexión
$conn = new mysqli($servidor, $usuario, $contraseña, $base_datos);

// Verificar conexión
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Error de conexión: " . $conn->connect_error]));
}

// Leer los datos enviados por JavaScript
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["action"])) {
    $action = $data["action"];

    if ($action === "crear") {
        $nombre = $data["nombre_usuario"];
        $email = $data["email_usuario"];
        $password = password_hash($data["contraseña_usuario"], PASSWORD_BCRYPT);
        $telefono = $data["telefono_usuario"];
        $institucion = $data["id_institucion"];
        $rol = $data["id_rol"];

        $sql = "INSERT INTO usuarios (nombre, email, contraseña, telefono, id_institucion, id_rol) 
                VALUES ('$nombre', '$email', '$password', '$telefono', '$institucion', '$rol')";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Usuario creado con éxito"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al crear usuario: " . $conn->error]);
        }
    }

    elseif ($action === "eliminar") {
        $id_usuario = $data["id_usuario"];

        $sql = "DELETE FROM usuarios WHERE id = '$id_usuario'";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Usuario eliminado con éxito"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar usuario: " . $conn->error]);
        }
    }

    elseif ($action === "editar") {
        $id_usuario = $data["id_usuario"];
        $nombre = $data["nombre_usuario"];
        $email = $data["email_usuario"];
        $telefono = $data["telefono_usuario"];
        $institucion = $data["id_institucion"];
        $rol = $data["id_rol"];

        $sql = "UPDATE usuarios SET 
                    nombre = '$nombre', 
                    email = '$email', 
                    telefono = '$telefono', 
                    id_institucion = '$institucion', 
                    id_rol = '$rol'
                WHERE id = '$id_usuario'";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Usuario actualizado con éxito"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar usuario: " . $conn->error]);
        }
    }
}

$conn->close();
?>