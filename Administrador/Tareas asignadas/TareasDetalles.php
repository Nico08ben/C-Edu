<?php
session_start();
// Redirigir si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php'); // Ajusta la ruta
    exit();
}

// Incluir el archivo de conexión a la base de datos
require_once(__DIR__ . '/../../conexion.php');

// INCLUIR LA FUNCIÓN PARA CREAR NOTIFICACIONES
// Ajusta esta ruta si es necesario, para que apunte a tu crear_notificacion.php
// Si TareasDetalles.php está en C:\xampp\htdocs\C-Edu\Docente\Tareas asignadas\
// y crear_notificacion.php está en C:\xampp\htdocs\C-Edu\PHP\api\
require_once(__DIR__ . '/../../PHP/api/crear_notificacion.php'); // Sube 2 niveles, luego PHP/api/


$task_details = null;
$error_message = '';
$success_message = ''; // Variable para mensajes de éxito
$stmt = null; // Inicializar statement a null
$result = null; // Inicializar resultado a null

$current_user_id = (int) $_SESSION['id_usuario']; // Obtener el ID del usuario actual
$nombre_usuario_actual = $_SESSION['nombre_usuario'] ?? 'Alguien'; // Para el mensaje de notificación

// --- INICIO: Lógica para cambiar el estado de la tarea ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['new_status'])) {
    $task_id_to_update = (int) $_POST['task_id'];
    $new_status = $_POST['new_status'];

    $allowed_statuses = ['Pendiente', 'Completada', 'Cancelada'];
    if (!in_array($new_status, $allowed_statuses)) {
        $error_message = "Estado de tarea no válido.";
    } else {
        if ($conn) {
            // Antes de actualizar, obtenemos el id_asignador y la instrucción de la tarea
            $id_asignador_original = null;
            $instruccion_tarea_original = "una tarea"; // Valor por defecto

            $sql_get_task_info = "SELECT id_asignador, instruccion_tarea FROM tarea WHERE id_tarea = ?";
            $stmt_get_info = $conn->prepare($sql_get_task_info);
            if ($stmt_get_info) {
                $stmt_get_info->bind_param("i", $task_id_to_update);
                if ($stmt_get_info->execute()) {
                    $result_info = $stmt_get_info->get_result();
                    if ($task_info_row = $result_info->fetch_assoc()) {
                        $id_asignador_original = (int) $task_info_row['id_asignador'];
                        $instruccion_tarea_original = $task_info_row['instruccion_tarea'];
                    }
                }
                $stmt_get_info->close();
            }

            // Solo actualizamos si la tarea está asignada al usuario actual O si el usuario actual es un administrador
            // (Para simplificar, aquí asumimos que solo el asignado puede cambiar el estado,
            //  pero si un admin pudiera cambiar el estado de cualquier tarea, la lógica de notificación
            //  aún se dirigiría al 'id_asignador_original')

            $sql_update = "UPDATE tarea SET estado_tarea = ? WHERE id_tarea = ? AND id_usuario = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) {
                $stmt_update->bind_param("sii", $new_status, $task_id_to_update, $current_user_id);
                if ($stmt_update->execute()) {
                    if ($stmt_update->affected_rows > 0) {
                        $success_message = "Estado de la tarea actualizado exitosamente a '" . htmlspecialchars($new_status) . "'.";

                        // ----- ¡AQUÍ ES DONDE CREAS LA NOTIFICACIÓN PARA EL ASIGNADOR ORIGINAL (ADMIN/DOCENTE)! -----
                        if ($id_asignador_original && $id_asignador_original !== $current_user_id) { // No notificar si el asignador se actualiza su propia tarea
                            $tipo_notificacion_param = 'tarea_actualizada';
                            $mensaje_notif_param = $nombre_usuario_actual . " ha actualizado el estado de la tarea '" . substr($instruccion_tarea_original, 0, 50) . "...' a: " . $new_status . ".";

                            // Enlace para que el asignador vea los detalles de esta tarea.
                            // Asumimos que el asignador (admin o docente) usa la misma vista de detalles.
                            // ¡VERIFICA Y AJUSTA ESTA RUTA URL! Debe apuntar a donde el ASIGNADOR vería los detalles.
                            $ruta_url_a_detalles_de_tarea = '/C-Edu/Docente/Tareas asignadas/'; // Ejemplo, podría ser una vista de admin también
                            $enlace_notif_param = $ruta_url_a_detalles_de_tarea . "TareasDetalles.php?id_tarea=" . $task_id_to_update;

                            if (crearNotificacion($conn, $id_asignador_original, $tipo_notificacion_param, $mensaje_notif_param, $enlace_notif_param)) {
                                error_log("Notificación creada para asignador $id_asignador_original por actualización de tarea $task_id_to_update. Enlace: $enlace_notif_param");
                            } else {
                                error_log("FALLO al crear notificación para asignador $id_asignador_original por actualización de tarea $task_id_to_update.");
                            }
                        }
                        // ----- FIN DE LA CREACIÓN DE LA NOTIFICACIÓN -----

                    } else {
                        $error_message = "No se pudo actualizar el estado. La tarea no existe o no está asignada a ti.";
                    }
                } else {
                    $error_message = "Error al ejecutar la consulta de actualización: " . $stmt_update->error;
                }
                $stmt_update->close();
            } else {
                $error_message = "Error al preparar la consulta de actualización: " . $conn->error;
            }
        } else {
            $error_message = "Error: No se pudo establecer la conexión a la base de datos para actualizar.";
        }
    }
}
// --- FIN: Lógica para cambiar el estado de la tarea ---


// --- INICIO: Lógica para obtener los detalles de la tarea ---
// (El resto del script para obtener y mostrar detalles permanece igual)
$id_tarea = $_GET['id_tarea'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) { // Si se recargó por el POST de actualización
    $id_tarea = $_POST['task_id'];
}

if ($id_tarea && !empty($id_tarea)) {
    if ($conn) {
        $sql = "SELECT t.*,
                        u_asignado.nombre_usuario AS nombre_asignado,
                        u_creador.nombre_usuario AS nombre_creador
                FROM tarea t
                INNER JOIN usuario u_asignado ON t.id_usuario = u_asignado.id_usuario
                INNER JOIN usuario u_creador ON t.id_asignador = u_creador.id_usuario
                WHERE t.id_tarea = ? AND (t.id_usuario = ? OR t.id_asignador = ?)"; // Permitir ver si eres el asignado O el asignador
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iii", $id_tarea, $current_user_id, $current_user_id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                if ($result && $result->num_rows > 0) {
                    $task_details = $result->fetch_assoc();
                } else {
                    $error_message = "No se encontraron detalles para la tarea solicitada, no está asignada a ti o no la creaste.";
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
    $error_message = "No se especificó un ID de tarea.";
}
// --- FIN: Lógica para obtener los detalles de la tarea ---


// --- INICIO: Lógica de cierre de statement y resultado mejorada ---
if ($result instanceof mysqli_result) {
    $result->free();
}
$result = null;
if ($stmt instanceof mysqli_stmt) {
    $stmt->close();
}
$stmt = null;
// --- FIN: Lógica de cierre de statement y resultado mejorada ---

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCENTE - Detalles de Tarea</title>
    <?php include "../../SIDEBAR/Docente/head.php"; // Asumiendo que está en Docente/Tareas asignadas/ ?>
    <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos CSS (sin cambios, son los mismos que ya tenías) */
        .task-detail-container {
            background-color: var(--sidebar-color);
            padding: 30px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .task-detail-container h2 {
            color: var(--primary-color);
            margin-bottom: 25px;
            border-bottom: 2px solid var(--primary-color-ligth);
            padding-bottom: 15px;
            font-size: 1.8rem;
        }

        .task-detail-item {
            margin-bottom: 20px;
            font-size: 1.1rem;
            color: var(--text-color);
            display: flex;
            align-items: flex-start;
        }

        .task-detail-item strong {
            color: var(--title-color);
            min-width: 180px;
            display: inline-block;
            margin-right: 10px;
            flex-shrink: 0;
        }

        .task-detail-item p {
            margin-top: 0;
            flex-grow: 1;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: .25rem;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: .25rem;
        }

        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: .25rem;
        }

        .status-update-form {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .status-update-form label {
            font-weight: bold;
            color: var(--title-color);
        }

        .status-update-form select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: var(--body-color);
            color: var(--text-color);
            cursor: pointer;
            outline: none;
            transition: border-color 0.3s ease;
        }

        body.dark .status-update-form select {
            background-color: var(--primary-color-ligth);
            border-color: #555;
            color: var(--text-color);
        }

        .status-update-form button {
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .status-update-form button:hover {
            background-color: #35a88d;
        }

        .btn-volver {
            display: inline-block;
            padding: 10px 20px;
            background: var(--primary-color);
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }

        .btn-volver:hover {
            background: #35a88d;
        }

        @media (max-width: 768px) {
            .task-detail-container {
                padding: 20px;
                margin-top: 15px;
            }

            .task-detail-container h2 {
                font-size: 1.5rem;
                margin-bottom: 20px;
                padding-bottom: 10px;
            }

            .task-detail-item {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 15px;
                font-size: 1rem;
            }

            .task-detail-item strong {
                min-width: unset;
                margin-right: 0;
                margin-bottom: 5px;
            }

            .status-update-form {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .status-update-form select,
            .status-update-form button {
                width: 100%;
            }

            .status-update-form label {
                margin-bottom: 0;
            }
        }
    </style>
</head>

<body>
    <?php include "../../SIDEBAR/Docente/sidebar.php"; ?>
    <section class="home">
        <div class="main-content">
            <div class="header">
                <h1 id="titulo1-header">DOCENTE - DETALLES DE TAREA</h1>
                <?php include '../../PHP/user_info.php'; ?>
            </div>

            <div class="task-detail-container">
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
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

                    <form action="TareasDetalles.php?id_tarea=<?php echo htmlspecialchars($task_details['id_tarea']); ?>"
                        method="post" class="status-update-form">
                        <input type="hidden" name="task_id"
                            value="<?php echo htmlspecialchars($task_details['id_tarea']); ?>">
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

                    <a href="index.php" class="btn-volver">Volver a la Lista de Tareas</a>

                <?php else: ?>
                    <?php if (!$error_message && !$success_message): // Mostrar solo si no hay otros mensajes ?>
                        <div class="alert alert-info">Cargando detalles de la tarea... o tarea no encontrada.</div>
                    <?php endif; ?>
                    <a href="index.php" class="btn-volver">Volver a la Lista de Tareas</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>

</html>