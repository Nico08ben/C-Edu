<?php
// task_details_view.php

// Incluir el archivo de conexión a la base de datos
require_once(__DIR__ . '/../../config/database.php'); // Asegúrate que $_SESSION esté disponible

// NO definas $page_title aquí si docente_tasks.php lo va a definir globalmente antes del <head>
// $page_title = "Detalles de Tarea - Docente"; // Esta línea se manejará en docente_tasks.php

$task_details = null;
$error_message_internal = ''; // Para errores de esta carga/actualización
$success_message_internal = ''; // Para éxitos de esta actualización

$current_user_id = $_SESSION['id_usuario']; // Obtener el ID del docente actual
$id_tarea_from_url = $_GET['id_tarea'] ?? null; // Siempre necesitamos el ID de la tarea

// --- INICIO: Lógica para cambiar el estado de la tarea (POST) ---
// Este bloque se ejecutará si la acción es 'update_status' y es un POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['new_status']) && isset($_GET['action']) && $_GET['action'] === 'update_status') {
    $task_id_to_update = $_POST['task_id'];
    $new_status = $_POST['new_status'];

    // Asegurarse que el id_tarea del POST coincida con el de la URL por seguridad (opcional pero bueno)
    if ($task_id_to_update != $id_tarea_from_url) {
        $_SESSION['task_detail_error_message'] = "Error: Inconsistencia en el ID de la tarea.";
        header("Location: docente_tasks.php?action=details&id_tarea=" . urlencode($id_tarea_from_url));
        exit();
    }

    $allowed_statuses = ['Pendiente', 'Completada', 'Cancelada'];
    if (!in_array($new_status, $allowed_statuses)) {
        $_SESSION['task_detail_error_message'] = "Estado de tarea no válido.";
    } else {
        if ($conn) {
            $sql_update = "UPDATE tarea SET estado_tarea = ? WHERE id_tarea = ? AND id_usuario = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) {
                $stmt_update->bind_param("sii", $new_status, $task_id_to_update, $current_user_id);
                if ($stmt_update->execute()) {
                    if ($stmt_update->affected_rows > 0) {
                        $_SESSION['task_detail_success_message'] = "Estado de la tarea actualizado exitosamente a '" . htmlspecialchars($new_status) . "'.";
                    } else {
                        $_SESSION['task_detail_error_message'] = "No se pudo actualizar el estado. La tarea no existe o no está asignada a ti.";
                    }
                } else {
                    $_SESSION['task_detail_error_message'] = "Error al ejecutar la consulta de actualización: " . $stmt_update->error;
                }
                if ($stmt_update instanceof mysqli_stmt) {
                    $stmt_update->close();
                }
            } else {
                $_SESSION['task_detail_error_message'] = "Error al preparar la consulta de actualización: " . $conn->error;
            }
        } else {
            $_SESSION['task_detail_error_message'] = "Error: No se pudo establecer la conexión a la base de datos para actualizar.";
        }
    }
    // REDIRIGIR SIEMPRE después del POST para evitar reenvío de formulario y asegurar estado limpio (Patrón POST-Redirect-GET)
    header("Location: docente_tasks.php?action=details&id_tarea=" . urlencode($task_id_to_update));
    exit();
}
// --- FIN: Lógica para cambiar el estado de la tarea ---


// --- INICIO: Lógica para obtener los detalles de la tarea (GET o después de fallo de POST sin redirección) ---
// Esto se ejecutará si es una solicitud GET para 'details' o si el bloque POST de arriba no redirigió (lo cual no debería pasar con PRG)
// $page_title se define en docente_tasks.php para la acción 'details'

// Recuperar mensajes de sesión (después de la redirección PRG)
if (isset($_SESSION['task_detail_success_message'])) {
    $success_message_internal = $_SESSION['task_detail_success_message'];
    unset($_SESSION['task_detail_success_message']);
}
if (isset($_SESSION['task_detail_error_message'])) {
    $error_message_internal = $_SESSION['task_detail_error_message'];
    unset($_SESSION['task_detail_error_message']);
}


if ($id_tarea_from_url && !empty($id_tarea_from_url)) {
    if ($conn) {
        $sql = "SELECT t.*,
                       u_asignado.nombre_usuario AS nombre_asignado,
                       u_creador.nombre_usuario AS nombre_creador
                FROM tarea t
                INNER JOIN usuario u_asignado ON t.id_usuario = u_asignado.id_usuario
                INNER JOIN usuario u_creador ON t.id_asignador = u_creador.id_usuario
                WHERE t.id_tarea = ? AND t.id_usuario = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $id_tarea_from_url, $current_user_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $task_details = $result->fetch_assoc();
                } else {
                    if (empty($error_message_internal)) { // No sobrescribir un error de actualización
                         $error_message_internal = "No se encontraron detalles para la tarea solicitada o no está asignada a ti.";
                    }
                }
                if ($result instanceof mysqli_result) $result->free();
            } else {
                $error_message_internal = "Error al ejecutar la consulta de detalles: " . $stmt->error;
            }
            if ($stmt instanceof mysqli_stmt) $stmt->close();
        } else {
            $error_message_internal = "Error al preparar la consulta de detalles: " . $conn->error;
        }
    } else {
        $error_message_internal = "Error: No se pudo establecer la conexión a la base de datos.";
    }
} else {
    if (empty($error_message_internal)) {
        $error_message_internal = "No se especificó un ID de tarea.";
    }
}
// --- FIN: Lógica para obtener los detalles de la tarea ---
?>

<div class="task-detail-container">
    <?php if (!empty($success_message_internal)): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message_internal); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_message_internal)): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error_message_internal); ?>
        </div>
    <?php endif; ?>

    <?php if ($task_details): ?>
        <h2><?php echo htmlspecialchars($task_details['instruccion_tarea']); ?></h2>
        <div class="task-detail-item">
            <strong>Creador:</strong> <?php echo htmlspecialchars($task_details['nombre_creador']); ?>
        </div>
        <div class="task-detail-item">
            <strong>Asignado a:</strong> <?php echo htmlspecialchars($task_details['nombre_asignado']); ?>
        </div>
        <div class="task-detail-item">
            <strong>Fecha de Inicio:</strong>
            <?php echo htmlspecialchars(date("d/m/Y", strtotime($task_details['fecha_inicio_tarea']))); ?>
        </div>
        <div class="task-detail-item">
            <strong>Fecha de Fin:</strong>
            <?php echo htmlspecialchars(date("d/m/Y", strtotime($task_details['fecha_fin_tarea']))); ?>
        </div>
        <div class="task-detail-item">
            <strong>Prioridad:</strong> <?php echo htmlspecialchars($task_details['prioridad']); ?>
        </div>
        <div class="task-detail-item">
            <strong>Instrucciones Completas:</strong>
            <p><?php echo nl2br(htmlspecialchars($task_details['instruccion_tarea'])); ?></p>
        </div>

        <form
            action="docente_tasks.php?action=update_status&id_tarea=<?php echo htmlspecialchars($task_details['id_tarea']); ?>"
            method="post" class="status-update-form">
            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_details['id_tarea']); ?>">
            <label for="new_status">Cambiar Estado:</label>
            <select id="new_status" name="new_status">
                <?php
                $statuses = ['Pendiente', 'Completada', 'Cancelada'];
                foreach ($statuses as $status) {
                    $selected = ($task_details['estado_tarea'] === $status) ? 'selected' : '';
                    echo "<option value='" . htmlspecialchars($status) . "' " . $selected . ">" . htmlspecialchars($status) . "</option>";
                }
                ?>
            </select>
            <button type="submit">Actualizar Estado</button>
        </form>
        <a href="docente_tasks.php" class="btn-volver">Volver a la Lista de Tareas</a>
    <?php else: ?>
        <?php if (empty($error_message_internal) && empty($success_message_internal)): // Solo si no hay otros mensajes ?>
            <div class="alert alert-info">Cargando detalles de la tarea... o tarea no encontrada.</div>
        <?php endif; ?>
        <a href="docente_tasks.php" class="btn-volver">Volver a la Lista de Tareas</a>
    <?php endif; ?>
</div>
<?php
// NO CIERRES </body> o </html> aquí si docente_tasks.php los provee.
// Asumiendo que esta vista es un fragmento.
// Los archivos originales tenían un </div></section></body></html> aquí, si es el caso,
// docente_tasks.php no debería tener esas etiquetas DESPUÉS de incluir esta vista.
?>