<?php
// Incluir el archivo de conexión a la base de datos
require_once(__DIR__ . '/../../config/database.php');

$task_details = null;
$error_message = '';
$success_message = ''; // Variable para mensajes de éxito
$mensaje = ''; // <-- Inicializar la variable $mensaje aquí
$stmt = null; // Inicializar statement a null
$result = null; // Inicializar resultado a null

// --- INICIO: Lógica para cambiar el estado de la tarea ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['new_status'])) {
    $task_id_to_update = $_POST['task_id'];
    $new_status = $_POST['new_status'];

    // Validar que el nuevo estado sea uno permitido según la base de datos
    $allowed_statuses = ['Pendiente', 'Completada', 'Cancelada']; // <-- AJUSTADO según tu base de datos
    if (!in_array($new_status, $allowed_statuses)) {
        $mensaje = "Estado de tarea no válido."; // Usar $mensaje para este tipo de error también
    } else {
        if ($conn) {
            // Consulta para actualizar el estado
            $sql_update = "UPDATE tarea SET estado_tarea = ? WHERE id_tarea = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) {
                $stmt_update->bind_param("si", $new_status, $task_id_to_update);
                if ($stmt_update->execute()) {
                     // Verificar si alguna fila fue afectada
                    if ($stmt_update->affected_rows > 0) {
                         $success_message = "Estado de la tarea actualizado exitosamente a '" . htmlspecialchars($new_status) . "'.";
                    } else {
                         // Esto podría ocurrir si la tarea_id no existe
                         $error_message = "No se pudo actualizar el estado. La tarea no existe.";
                    }
                    // No redirigimos para que el usuario vea el mensaje y el estado actualizado en la misma página
                } else {
                    $mensaje = "Error al ejecutar la consulta de actualización: " . $stmt_update->error; // Usar $mensaje
                }
                $stmt_update->close();
            } else {
                $mensaje = "Error al preparar la consulta de actualización: " . $conn->error; // Usar $mensaje
            }
        } else {
            $mensaje = "Error: No se pudo establecer la conexión a la base de datos para actualizar."; // Usar $mensaje
        }
    }
}
// --- FIN: Lógica para cambiar el estado de la tarea ---


// --- INICIO: Lógica para obtener los detalles de la tarea ---
// Obtener el ID de la tarea de la URL (si se accede por GET) o del POST (después de una actualización)
$id_tarea = $_GET['id_tarea'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $id_tarea = $_POST['task_id'];
}


if ($id_tarea && !empty($id_tarea)) {

    if ($conn) {
        // Consulta para obtener detalles de la tarea, nombre del asignado y nombre del creador
        $sql = "SELECT t.*,
                        u_asignado.nombre_usuario AS nombre_asignado,
                        u_creador.nombre_usuario AS nombre_creador
                FROM tarea t
                INNER JOIN usuario u_asignado ON t.id_usuario = u_asignado.id_usuario
                INNER JOIN usuario u_creador ON t.id_asignador = u_creador.id_usuario
                WHERE t.id_tarea = ?"; // Filtra por el ID de la tarea

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $id_tarea);
            if ($stmt->execute()) {
                 $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $task_details = $result->fetch_assoc();
                } else {
                    // Si no se encuentran detalles, establecer un mensaje de error
                    $error_message = "No se encontraron detalles para la tarea solicitada.";
                }
            } else {
                 $error_message = "Error al ejecutar la consulta preparada: " . $stmt->error;
            }

        } else {
            $error_message = "Error al preparar la consulta de detalles: " . $conn->error;
        }
    } else {
        $error_message = "Error: No se pudo establecer la conexión a la base de datos.";
    }
} else {
    // Si no se especifica un ID de tarea, establecer un mensaje de error
    $error_message = "No se especificó un ID de tarea.";
}
// --- FIN: Lógica para obtener los detalles de la tarea ---


// --- INICIO: Lógica de cierre de statement y resultado mejorada ---
// Liberar resultado si fue obtenido y es un objeto válido
if ($result instanceof mysqli_result) {
    $result->free();
}
// Establecer resultado a null después de usarlo
$result = null;

// Cerrar statement si fue preparado y es un objeto válido
if ($stmt instanceof mysqli_stmt) {
    $stmt->close();
}
// Establecer statement a null después de usarlo
$stmt = null;
// --- FIN: Lógica de cierre de statement y resultado mejorada ---


// $conn->close(); // Cerrar conexión si no se necesita más en este script
?>
            <div class="task-detail-container">
                <?php if ($mensaje): // Mostrar mensajes de error o éxito de la actualización ?>
                    <div class="alert <?php echo (strpos($mensaje, 'Error') !== false) ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                 <?php if ($success_message): // Mostrar mensaje de éxito de la actualización de estado ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                 <?php if ($error_message && !$success_message): // Mostrar mensaje de error si no hubo éxito ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error_message); ?>
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

                    <form action="admin_tasks.php?action=update_status&id_tarea=<?php echo htmlspecialchars($task_details['id_tarea']); ?>" method="post" class="status-update-form">
                        <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_details['id_tarea']); ?>">
                        <label for="new_status">Cambiar Estado:</label>
                        <select id="new_status" name="new_status">
                            <?php
                            // Define los estados posibles según tu base de datos
                            $statuses = ['Pendiente', 'Completada', 'Cancelada']; // <-- AJUSTADO según tu base de datos
                            foreach ($statuses as $status) {
                                $selected = ($task_details['estado_tarea'] === $status) ? 'selected' : '';
                                echo "<option value='" . htmlspecialchars($status) . "' " . $selected . ">" . htmlspecialchars($status) . "</option>";
                            }
                            ?>
                        </select>
                        <button type="submit">Actualizar Estado</button>
                    </form>

                    <a href="admin_tasks.php" class="btn-volver">Volver a la Lista de Tareas</a>

                <?php else: // Mostrar mensajes de error si no se encontraron detalles ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php else: ?>
                         <div class="alert alert-info">Cargando detalles de la tarea... o tarea no encontrada.</div>
                    <?php endif; ?>
                    <a href="admin_tasks.php" class="btn-volver">Volver a la Lista de Tareas</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
