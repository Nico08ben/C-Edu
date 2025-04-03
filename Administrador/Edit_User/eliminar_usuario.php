<?php
include '../../conexion.php'; // Conexión a la base de datos

// Establecer charset para manejar caracteres especiales
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar que tenemos un ID de usuario válido
    if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
        echo "Error: No se proporcionó ID de usuario";
        exit();
    }
    
    $id = (int)$_POST['id_usuario'];
    
    // Eliminar usuario de la base de datos
    $sql = "DELETE FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "Usuario eliminado correctamente";
    } else {
        echo "Error al eliminar usuario: " . $stmt->error;
    }
    
    $stmt->close();
} else {
    echo "Método no permitido";
}

// Cerrar conexión
$conn->close();
?>