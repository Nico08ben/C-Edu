<?php
session_start();
include '../../conexion.php';
$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
        die("Error: No se proporcionó ID de usuario");
    }
    
    $id = (int)$_POST['id_usuario'];
    
    $conn->begin_transaction(); // Iniciar transacción
    
    try {
        // Paso 1: Eliminar tareas asociadas al usuario
        $sql_delete_tareas = "DELETE FROM tarea WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql_delete_tareas);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Paso 2: Eliminar mensajes donde el usuario es receptor
        $sql_delete_mensajes = "DELETE FROM mensaje WHERE id_receptor = ?";
        $stmt = $conn->prepare($sql_delete_mensajes);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Paso 3: Eliminar eventos relacionados
        $sql_delete_eventos = "DELETE FROM evento WHERE id_responsable = ?";
        $stmt = $conn->prepare($sql_delete_eventos);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        // Paso 4: Eliminar el usuario
        $sql_delete_usuario = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt = $conn->prepare($sql_delete_usuario);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $conn->commit(); // Confirmar cambios
        echo "Usuario eliminado correctamente";
    } catch (Exception $e) {
        $conn->rollback(); // Revertir en caso de error
        die("Error al eliminar usuario: " . $e->getMessage());
    }
    
    $stmt->close();
} else {
    echo "Método no permitido";
}

$conn->close();
?>