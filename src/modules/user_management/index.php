<?php
require_once(__DIR__ . '/../../config/database.php');
    
if (!$conn) { /* ... manejo de error ... */ }

// Leer datos
$data = json_decode(file_get_contents("php://input"), true);

if (isset($data["action"])) {
    $action = $data["action"];

    // ... (conexión a la BD y otras partes del código) ...

if ($action === "crear") {
    // Corrección: Usar nombres de columnas según la tabla 'usuario'
    $nombre = $data["nombre_usuario"]; // No necesitas $conn->real_escape_string con sentencias preparadas
    $email = $data["email_usuario"];
    $password = password_hash($data["contraseña_usuario"], PASSWORD_BCRYPT);
    $telefono = $data["telefono_usuario"];
    $institucion = (int)$data["id_institucion"];
    $rol = (int)$data["id_rol"];
    // Asumimos que id_materia puede ser opcional
    $id_materia = isset($data["id_materia"]) ? (int)$data["id_materia"] : null;

    // Corrección: Nombre de la tabla 'usuario' y columnas
    // Ajusta la consulta si id_materia puede ser NULL y la columna en la BD lo permite
    if ($id_materia === null) {
        $sql = "INSERT INTO usuario (nombre_usuario, email_usuario, contraseña_usuario, telefono_usuario, id_institucion, id_rol, id_materia)
                VALUES (?, ?, ?, ?, ?, ?, NULL)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            // Manejo de error en la preparación
            echo json_encode(["status" => "error", "message" => "Error al preparar la consulta: " . $conn->error]);
            exit;
        }
        // "ssssii" - s para string, i para integer. Ajusta según tus tipos de datos.
        $stmt->bind_param("ssssii", $nombre, $email, $password, $telefono, $institucion, $rol);
    } else {
        $sql = "INSERT INTO usuario (nombre_usuario, email_usuario, contraseña_usuario, telefono_usuario, id_institucion, id_rol, id_materia)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(["status" => "error", "message" => "Error al preparar la consulta: " . $conn->error]);
            exit;
        }
        // "ssssiii" si id_materia es un entero
        $stmt->bind_param("ssssiii", $nombre, $email, $password, $telefono, $institucion, $rol, $id_materia);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Usuario creado con éxito", "id" => $stmt->insert_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al crear usuario: " . $stmt->error]);
    }
    $stmt->close();
}
// ... resto de las acciones (eliminar, editar) deben seguir el mismo patrón de sentencias preparadas ...
elseif ($action === "eliminar") {
    $id_usuario = (int)$data["id_usuario"];

    // Corrección: Nombre de la tabla 'usuario'
    // Ya que la eliminación en delete_user_handler.php es más compleja (maneja transacciones y tablas relacionadas),
    // podrías considerar llamar a la lógica de ese handler desde aquí si es apropiado,
    // o replicar una lógica simplificada SÓLO SI este endpoint tiene un propósito diferente
    // y la eliminación completa se maneja en otro lado.
    // Por ahora, lo haremos simple y seguro:
    $sql = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de eliminación: " . $conn->error]);
        exit;
    }
    $stmt->bind_param("i", $id_usuario);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
             echo json_encode(["status" => "success", "message" => "Usuario eliminado con éxito"]);
        } else {
             echo json_encode(["status" => "info", "message" => "Usuario no encontrado o ya eliminado."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Error al eliminar usuario: " . $stmt->error]);
    }
    $stmt->close();
}

    elseif ($action === "editar") {
        $id_usuario = (int)$data["id_usuario"];
        $nombre = $data["nombre_usuario"];
        $email = $data["email_usuario"];
        $telefono = $data["telefono_usuario"];
        $institucion = (int)$data["id_institucion"];
        $rol = (int)$data["id_rol"];
        $id_materia = isset($data["id_materia"]) ? (int)$data["id_materia"] : null;
    
        // Construir la parte SET de la consulta dinámicamente si es necesario
        $sql_parts = [
            "nombre_usuario = ?",
            "email_usuario = ?",
            "telefono_usuario = ?",
            "id_institucion = ?",
            "id_rol = ?"
        ];
        $params = [$nombre, $email, $telefono, $institucion, $rol];
        $types = "sssii"; // Ajusta según los tipos de datos
    
        if ($id_materia !== null) {
            $sql_parts[] = "id_materia = ?";
            $params[] = $id_materia;
            $types .= "i";
        } else {
            // Si quieres establecer id_materia a NULL explícitamente cuando no se proporciona:
            $sql_parts[] = "id_materia = NULL";
            // No se añade a $params ni a $types para este caso, ya que NULL se pone directo en SQL
        }
        // Siempre añadir el id_usuario al final para el WHERE
        $params[] = $id_usuario;
        $types .= "i";
    
        $sql = "UPDATE usuario SET " . implode(", ", $sql_parts) . " WHERE id_usuario = ?";
    
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo json_encode(["status" => "error", "message" => "Error al preparar la consulta de actualización: " . $conn->error]);
            exit;
        }
    
        // El operador '...' (splat) expande el array de parámetros
        $stmt->bind_param($types, ...$params);
    
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode(["status" => "success", "message" => "Usuario actualizado con éxito"]);
            } else {
                echo json_encode(["status" => "info", "message" => "No se realizaron cambios o el usuario no existe."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Error al actualizar usuario: " . $stmt->error]);
        }
        $stmt->close();
    }
    }
    $conn->close();
?>