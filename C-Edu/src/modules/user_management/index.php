<?php
// Iniciar sesión si es necesario para CSRF u otras validaciones de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../config/database.php');
header('Content-Type: application/json');

if (!$conn) { /* ... manejo de error ... */ }

// Leer datos
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["action"])) {
    $action = $data["action"];

    if ($action === "crear") {
        // Corrección: Usar nombres de columnas según la tabla 'usuario'
        $nombre = $conn->real_escape_string($data["nombre_usuario"]);
        $email = $conn->real_escape_string($data["email_usuario"]);
        $password = password_hash($data["contraseña_usuario"], PASSWORD_BCRYPT);
        $telefono = $conn->real_escape_string($data["telefono_usuario"]);
        $institucion = (int)$data["id_institucion"];
        $rol = (int)$data["id_rol"];

        // Corrección: Nombre de la tabla 'usuario' y columnas
        $sql = "INSERT INTO usuario (nombre_usuario, email_usuario, contraseña_usuario, telefono_usuario, id_institucion, id_rol) 
                VALUES ('$nombre', '$email', '$password', '$telefono', $institucion, $rol)";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Usuario creado con éxito"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al crear usuario: " . $conn->error]);
        }
    }

    elseif ($action === "eliminar") {
        $id_usuario = (int)$data["id_usuario"];

        // Corrección: Nombre de la tabla 'usuario'
        $sql = "DELETE FROM usuario WHERE id_usuario = $id_usuario";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Usuario eliminado con éxito"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al eliminar usuario: " . $conn->error]);
        }
    }

    elseif ($action === "editar") {
        $id_usuario = (int)$data["id_usuario"];
        $nombre = $conn->real_escape_string($data["nombre_usuario"]);
        $email = $conn->real_escape_string($data["email_usuario"]);
        $telefono = $conn->real_escape_string($data["telefono_usuario"]);
        $institucion = (int)$data["id_institucion"];
        $rol = (int)$data["id_rol"];

        // Corrección: Nombre de la tabla y columnas
        $sql = "UPDATE usuario SET 
                    nombre_usuario = '$nombre', 
                    email_usuario = '$email', 
                    telefono_usuario = '$telefono', 
                    id_institucion = $institucion, 
                    id_rol = $rol
                WHERE id_usuario = $id_usuario";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "Usuario actualizado con éxito"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar usuario: " . $conn->error]);
        }
    }
}

$conn->close();
?>