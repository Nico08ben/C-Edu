<?php
// if (session_status() === PHP_SESSION_NONE) { session_start(); } // Ya iniciado por el script que lo incluye

require_once(__DIR__ . '/../../config/database.php');

if (!$conn) {
    // $_SESSION['user_management_message'] = "Error crítico de conexión.";
    // $_SESSION['user_management_message_type'] = "error";
    // header('Location: admin_user_management.php');
    // exit();
    die("Error crítico de conexión."); // Temporal, idealmente usar sesión/redirección
}

// Nota: Idealmente, la validación CSRF también debería estar aquí si este script
// se puede llamar directamente de alguna forma, aunque si es solo incluido por
// public/admin_user_management.php y esa página tiene su propia lógica CSRF para acciones POST,
// podría ser suficiente. Por seguridad, añadirlo aquí no estaría de más.
// if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
//     $_SESSION['user_management_message'] = "Error de seguridad: Token CSRF inválido.";
//     $_SESSION['user_management_message_type'] = "error";
//     header('Location: admin_user_management.php');
//     exit();
// }


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->set_charset("utf8mb4");

    if (!isset($_POST['id_usuario']) || empty($_POST['id_usuario'])) {
        $_SESSION['user_management_message'] = "Error: No se proporcionó ID de usuario para eliminar.";
        $_SESSION['user_management_message_type'] = "error";
        header('Location: admin_user_management.php');
        exit();
    }
    
    $id = (int)$_POST['id_usuario'];
    
    $conn->begin_transaction(); 
    
    try {
        // Para simplificar, asumimos que el último statement es el de eliminar usuario para el close()
        // En una implementación real, cada $stmt debería cerrarse después de su uso.

        // Paso 1: Eliminar tareas donde el usuario es el ASIGNADO (id_usuario en tarea)
        $sql_delete_tareas_asignadas = "DELETE FROM tarea WHERE id_usuario = ?";
        $stmt1 = $conn->prepare($sql_delete_tareas_asignadas);
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();
        
        // Paso 2: Eliminar tareas donde el usuario es el ASIGNADOR (id_asignador en tarea)
        // Esto podría ser opcional o manejado de otra forma (reasignar, etc.)
        // Por ahora, lo comentaré, ya que podría no ser el comportamiento deseado sin más contexto.
        // $sql_delete_tareas_creadas = "DELETE FROM tarea WHERE id_asignador = ?";
        // $stmt2 = $conn->prepare($sql_delete_tareas_creadas);
        // $stmt2->bind_param("i", $id);
        // $stmt2->execute();
        // $stmt2->close();
        
        // Paso 3: Eliminar mensajes donde el usuario es receptor O emisor
        $sql_delete_mensajes_receptor = "DELETE FROM mensaje WHERE id_receptor = ?";
        $stmt3 = $conn->prepare($sql_delete_mensajes_receptor);
        $stmt3->bind_param("i", $id);
        $stmt3->execute();
        $stmt3->close();

        $sql_delete_mensajes_emisor = "DELETE FROM mensaje WHERE id_emisor = ?";
        $stmt4 = $conn->prepare($sql_delete_mensajes_emisor);
        $stmt4->bind_param("i", $id);
        $stmt4->execute();
        $stmt4->close();
        
        // Paso 4: Eliminar eventos relacionados
        $sql_delete_eventos = "DELETE FROM evento WHERE id_responsable = ?";
        $stmt5 = $conn->prepare($sql_delete_eventos);
        $stmt5->bind_param("i", $id);
        $stmt5->execute();
        $stmt5->close();
        
        // Paso 5: Eliminar el usuario
        $sql_delete_usuario = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt_final = $conn->prepare($sql_delete_usuario); // Renombrado para claridad
        $stmt_final->bind_param("i", $id);
        $stmt_final->execute();
        
        if ($stmt_final->affected_rows > 0) {
            $conn->commit();
            $_SESSION['user_management_message'] = "Usuario y sus datos relacionados eliminados correctamente.";
            $_SESSION['user_management_message_type'] = "success";
        } else {
            // Si no se afectaron filas, el usuario podría no haber existido o ya fue eliminado.
            // Si las consultas anteriores fallaron, la excepción ya se habría capturado.
            $conn->rollback(); // Aún así, revertir por si alguna consulta anterior tuvo un error no fatal
            $_SESSION['user_management_message'] = "El usuario no pudo ser eliminado (quizás ya no existía).";
            $_SESSION['user_management_message_type'] = "error";
        }
        $stmt_final->close();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['user_management_message'] = "Error al eliminar usuario: " . $e->getMessage();
        $_SESSION['user_management_message_type'] = "error";
    }
    
} else {
    $_SESSION['user_management_message'] = "Método no permitido para eliminar.";
    $_SESSION['user_management_message_type'] = "error";
}

$conn->close();
header('Location: admin_user_management.php'); // Redirige a la lista de usuarios
exit();
?>