<?php
include '../../conexion.php'; // Conexión a la base de datos

// Establecer charset para manejar caracteres especiales
$conn->set_charset("utf8mb4");

// Para depuración - registrar todos los datos recibidos
$debug_log = "Datos recibidos en actualizar_usuario.php: ";
foreach ($_POST as $key => $value) {
    $debug_log .= "$key: $value, ";
}
error_log($debug_log);

session_start();
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Token CSRF inválido");
}

// Verificar si se recibieron datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar que tenemos un ID de usuario válido
    if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
        echo "Error: No se proporcionó ID de usuario";
        error_log("Error: No se proporcionó ID de usuario");
        $conn->close();
        exit();
    }
    
    $id = (int)$_POST['id_usuario'];
    
    // Verificar que el ID existe en la base de datos
    $checkId = $conn->query("SELECT id_usuario FROM usuario WHERE id_usuario = $id");
    if ($checkId->num_rows == 0) {
        echo "Error: No se encontró ningún usuario con ID $id";
        error_log("Error: No se encontró usuario con ID $id");
        $conn->close();
        exit();
    }
    
    // Obtener los datos del formulario y sanitizarlos
    $nombre = $conn->real_escape_string($_POST['nombre_usuario']);
    $email = $conn->real_escape_string($_POST['email_usuario']);
    $telefono = isset($_POST['telefono_usuario']) ? $conn->real_escape_string($_POST['telefono_usuario']) : '';
    $institucion = (int)$_POST['id_institucion'];
    $rol = (int)$_POST['id_rol'];
    $materia = isset($_POST['id_materia']) ? (int)$_POST['id_materia'] : 0;
    
    // Iniciar la consulta SQL base
    $sql = "UPDATE usuario SET 
            nombre_usuario = '$nombre',
            email_usuario = '$email',
            telefono_usuario = '$telefono',
            id_institucion = $institucion,
            id_rol = $rol";
    
    // Añadir materia si existe en el formulario
    if ($materia > 0) {
        $sql .= ", id_materia = $materia";
    }
    
    // Agregar contraseña a la actualización solo si se proporcionó una nueva
    if (isset($_POST['password_usuario']) && !empty($_POST['password_usuario'])) {
        $password = password_hash($_POST['password_usuario'], PASSWORD_DEFAULT);
        $sql .= ", contraseña_usuario = '$password'";
    }
    
    // Completar la consulta con la condición WHERE
    $sql .= " WHERE id_usuario = $id";
    
    error_log("SQL Update: $sql");
    
    // Ejecutar la consulta
    if ($conn->query($sql) === TRUE) {
        echo "Usuario actualizado correctamente";
    } else {
        echo "Error al actualizar usuario: " . $conn->error;
        error_log("Error SQL update: " . $conn->error);
    }
} else {
    echo "Método no permitido";
}

// Cerrar conexión
$conn->close();
?>