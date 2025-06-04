<?php
session_start();

// Redirigir si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php'); // Ajusta la ruta a la página de inicio de sesión
    exit();
}

// Incluir el archivo de conexión a la base de datos
require_once(__DIR__ . '/../../conexion.php');

// INCLUIR LA FUNCIÓN PARA CREAR NOTIFICACIONES
require_once(__DIR__ . '/../../PHP/api/crear_notificacion.php'); // Sube 2 niveles, luego PHP/api/

$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
        $user_role_is_admin = true; // Flag para determinar si el usuario actual es admin
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
        $user_role_is_admin = false; // Flag para determinar si el usuario actual es admin
    } else {
        // Manejar otros roles si es necesario, por defecto no es admin
        $user_role_is_admin = false;
    }
} else {
    // Si no hay rol, no es admin (o manejar según tu lógica de seguridad)
    $user_role_is_admin = false;
}

$task_details = null;
$error_message = '';
$success_message = ''; // Variable para mensajes de éxito
$stmt = null; // Inicializar statement a null
$result = null; // Inicializar resultado a null

$current_user_id = (int) $_SESSION['id_usuario']; // Obtener el ID del usuario actual
$nombre_usuario_actual = $_SESSION['nombre_usuario'] ?? 'Alguien'; // Para el mensaje de notificación

// --- INICIO: Lógica para cambiar el estado de la tarea (puede ser realizada por el asignado o por un admin) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['new_status'])) {
    $task_id_to_update = (int) $_POST['task_id'];
    $new_status = $_POST['new_status'];

    $allowed_statuses = ['Pendiente', 'Completada', 'Cancelada'];
    if (!in_array($new_status, $allowed_statuses)) {
        $error_message = "Estado de tarea no válido.";
    } else {
        if ($conn) {
            // Antes de actualizar, obtenemos el id_asignador, id_usuario (asignado), y la instrucción de la tarea
            $id_asignador_original = null;
            $id_usuario_asignado = null; // ID del usuario a quien está asignada la tarea
            $instruccion_tarea_original = "una tarea"; // Valor por defecto

            $sql_get_task_info = "SELECT id_asignador, id_usuario, instruccion_tarea FROM tarea WHERE id_tarea = ?";
            $stmt_get_info = $conn->prepare($sql_get_task_info);
            if ($stmt_get_info) {
                $stmt_get_info->bind_param("i", $task_id_to_update);
                if ($stmt_get_info->execute()) {
                    $result_info = $stmt_get_info->get_result();
                    if ($task_info_row = $result_info->fetch_assoc()) {
                        $id_asignador_original = (int) $task_info_row['id_asignador'];
                        $id_usuario_asignado = (int) $task_info_row['id_usuario'];
                        $instruccion_tarea_original = $task_info_row['instruccion_tarea'];
                    }
                }
                $stmt_get_info->close();
            }

            // La condición para actualizar la tarea:
            // Si el usuario actual es un administrador, puede actualizar cualquier tarea.
            // Si no es administrador, solo puede actualizar la tarea si es el usuario asignado.
            $sql_update = "UPDATE tarea SET estado_tarea = ? WHERE id_tarea = ?";
            if (!$user_role_is_admin) {
                // Si no es admin, añade la condición de que la tarea le pertenezca
                $sql_update .= " AND id_usuario = ?";
            }
            
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) {
                if ($user_role_is_admin) {
                    $stmt_update->bind_param("si", $new_status, $task_id_to_update);
                } else {
                    $stmt_update->bind_param("sii", $new_status, $task_id_to_update, $current_user_id);
                }
                
                if ($stmt_update->execute()) {
                    if ($stmt_update->affected_rows > 0) {
                        $success_message = "Estado de la tarea actualizado exitosamente a '" . htmlspecialchars($new_status) . "'.";

                        // --- Lógica de Notificación ---
                        // Notificar al asignador original si la tarea fue actualizada por el asignado
                        // O notificar al asignado si la tarea fue actualizada por el asignador (admin)
                        $notificar_a_id = null;
                        $mensaje_notif = "";
                        $enlace_notif = "";

                        // Si el que actualiza es el asignado y hay un asignador distinto
                        if ($current_user_id === $id_usuario_asignado && $id_asignador_original !== $current_user_id) {
                            $notificar_a_id = $id_asignador_original;
                            $mensaje_notif = $nombre_usuario_actual . " ha actualizado el estado de la tarea '" . substr($instruccion_tarea_original, 0, 50) . "...' a: " . $new_status . ".";
                            // Enlace para el asignador (puede ser un admin o docente)
                            // Ajusta esta ruta si tienes una página de detalles diferente para el admin.
                            $enlace_notif = '/C-Edu/Administrador/Tareas%20Asignadas/TareasDetalles.php?id_tarea=' . $task_id_to_update; // Asumimos que el admin vería desde su TareasDetalles
                        } 
                        // Si el que actualiza es el asignador (admin) y el asignado es distinto
                        else if ($current_user_id === $id_asignador_original && $id_usuario_asignado !== $current_user_id) {
                            $notificar_a_id = $id_usuario_asignado;
                            $mensaje_notif = $nombre_usuario_actual . " (Admin) ha actualizado el estado de la tarea '" . substr($instruccion_tarea_original, 0, 50) . "...' a: " . $new_status . ".";
                            // Enlace para el asignado (docente)
                            $enlace_notif = '/C-Edu/Docente/Tareas%20Asignadas/TareasDetalles.php?id_tarea=' . $task_id_to_update;
                        }
                        // Si un admin actualiza una tarea que no creó y no le fue asignada, y hay un asignador distinto
                        else if ($user_role_is_admin && $current_user_id !== $id_asignador_original && $current_user_id !== $id_usuario_asignado) {
                             // Notificar tanto al asignador como al asignado
                            if ($id_asignador_original) {
                                $mensaje_notif_asignador = $nombre_usuario_actual . " (Admin) ha actualizado el estado de la tarea '" . substr($instruccion_tarea_original, 0, 50) . "...' a: " . $new_status . ".";
                                $enlace_notif_asignador = '/C-Edu/Administrador/Tareas%20Asignadas/TareasDetalles.php?id_tarea=' . $task_id_to_update; // Ruta para el asignador (si es admin)
                                crearNotificacion($conn, $id_asignador_original, 'tarea_actualizada', $mensaje_notif_asignador, $enlace_notif_asignador);
                            }
                            if ($id_usuario_asignado) {
                                $mensaje_notif_asignado = $nombre_usuario_actual . " (Admin) ha actualizado el estado de la tarea '" . substr($instruccion_tarea_original, 0, 50) . "...' a: " . $new_status . ".";
                                $enlace_notif_asignado = '/C-Edu/Docente/Tareas%20Asignadas/TareasDetalles.php?id_tarea=' . $task_id_to_update; // Ruta para el asignado (si es docente)
                                crearNotificacion($conn, $id_usuario_asignado, 'tarea_actualizada', $mensaje_notif_asignado, $enlace_notif_asignado);
                            }
                        }


                        if ($notificar_a_id && crearNotificacion($conn, $notificar_a_id, 'tarea_actualizada', $mensaje_notif, $enlace_notif)) {
                            error_log("Notificación creada para usuario $notificar_a_id por actualización de tarea $task_id_to_update. Enlace: $enlace_notif");
                        } else if ($notificar_a_id) { // Solo si $notificar_a_id no es null
                             error_log("FALLO al crear notificación para usuario $notificar_a_id por actualización de tarea $task_id_to_update.");
                        }
                        // --- FIN Lógica de Notificación ---

                    } else {
                        $error_message = "No se pudo actualizar el estado. La tarea no existe o no tienes permiso para modificarla.";
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
                INNER JOIN usuario u_creador ON t.id_asignador = u_creador.id_usuario";
        
        // La condición WHERE depende del rol del usuario
        if ($user_role_is_admin) {
            // El administrador puede ver cualquier tarea por su ID
            $sql .= " WHERE t.id_tarea = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $id_tarea);
            }
        } else {
            // Un docente solo puede ver las tareas que le fueron asignadas o que él mismo asignó
            $sql .= " WHERE t.id_tarea = ? AND (t.id_usuario = ? OR t.id_asignador = ?)";
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("iii", $id_tarea, $current_user_id, $current_user_id);
            }
        }

        if ($stmt) {
            if ($stmt->execute()) {
                $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $task_details = $result->fetch_assoc();
                } else {
                    // Si no se encuentran detalles, establecer un mensaje de error
                    $error_message = "No se encontraron detalles para la tarea solicitada.";
                    if (!$user_role_is_admin) {
                        $error_message .= " No está asignada a ti o no la creaste.";
                    }
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
<html lang="es" class="<?php echo $theme_class;?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($user_role_is_admin ? 'ADMIN' : 'DOCENTE'); ?> - Detalles de Tarea</title>
    <?php
    // Incluir el head y sidebar según el rol
    if ($user_role_is_admin) {
        include "../../SIDEBAR/Admin/head.php";
    } else {
        include "../../SIDEBAR/Docente/head.php";
    }
    ?>
    <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
        <style>
        /* Estilos adicionales para la página de detalles */
        .task-detail-container {
            background-color: var(--bg-content); /* Usando color de sidebar */
            padding: 30px; /* Aumentar padding */
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Sombra más pronunciada */
            max-width: 800px; /* Limitar ancho máximo */
            margin-left: auto; /* Centrar contenedor */
            margin-right: auto; /* Centrar contenedor */
        }

        .task-detail-container h2 {
            color: var(--text-primary);
            margin-bottom: 25px; /* Aumentar margen */
            border-bottom: 2px solid var(--text-disabled); /* Usando la variable light */
            padding-bottom: 15px; /* Aumentar padding */
            font-size: 1.8rem; /* Aumentar tamaño de fuente */
        }

        .task-detail-item {
            margin-bottom: 20px; /* Aumentar margen */
            font-size: 1.1rem; /* Aumentar tamaño de fuente */
            color: var(--text-secondary); /* Usando la variable de texto */
            display: flex; /* Usar flexbox para alinear label y valor */
            align-items: flex-start; /* Alinear al inicio */
        }

        .task-detail-item strong {
            color: var(--text-primary); /* Usando la variable de título */
            min-width: 180px; /* Aumentar ancho mínimo para las etiquetas */
            display: inline-block;
            margin-right: 10px; /* Espacio entre label y valor */
            flex-shrink: 0; /* Evitar que la etiqueta se encoja */
        }

         .task-detail-item p {
             margin-top: 0; /* Eliminar margen superior por defecto del párrafo */
             flex-grow: 1; /* Permitir que el párrafo ocupe el espacio restante */
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
            margin-top: 30px; /* Espacio encima del formulario de estado */
            padding-top: 20px;
            border-top: 1px solid #eee; /* Línea separadora */
            display: flex; /* Usar flexbox para alinear elementos del formulario */
            align-items: center; /* Centrar verticalmente */
            gap: 15px; /* Espacio entre elementos */
            flex-wrap: wrap; /* Permite que los elementos se envuelvan */
        }

        .status-update-form label {
            font-weight: bold;
            color: var(--text-primary);
        }

        .status-update-form select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: var(--bg-body); /* Fondo del select */
            color: var(--text-primary); /* Color de texto del select */
            cursor: pointer;
            outline: none;
            transition: border-color 0.3s ease;
        }

         body.dark .status-update-form select {
             background-color: var(--scrollbar-thumb); /* Fondo oscuro para select en modo oscuro */
             border-color: #555; /* Borde más oscuro en modo oscuro */
             color: var(--text-primary); /* Color de texto en modo oscuro */
         }


        .status-update-form button {
            padding: 10px 20px;
            background: var(--role-button-primary-bg);
            color: white;
            border: none;
            font-size: 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .status-update-form button:hover {
            background-color: #35a88d; /* Tono más oscuro para hover (ajustado al nuevo color) */
        }

        .btn-volver {
            display: inline-block; /* Asegurar que padding y margin funcionen */
            padding: 10px 20px;
            background: var(--role-button-primary-bg); /* Usando la variable primary */
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
            background: #35a88d; /* Un tono ligeramente más oscuro para hover (ajustado al nuevo color) */
        }

        /* Estilos responsivos para detalles */
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
                flex-direction: column; /* Apila label y valor en móvil */
                align-items: flex-start; /* Alinea al inicio */
                margin-bottom: 15px;
                font-size: 1rem;
            }

            .task-detail-item strong {
                min-width: unset; /* Elimina el ancho mínimo */
                margin-right: 0; /* Elimina el margen a la derecha */
                margin-bottom: 5px; /* Espacio debajo de la etiqueta */
            }

            .status-update-form {
                flex-direction: column; /* Apila elementos del formulario en móvil */
                align-items: stretch; /* Estira elementos */
                gap: 10px;
            }

            .status-update-form select,
            .status-update-form button {
                width: 100%; /* Ocupa todo el ancho */
            }

            .status-update-form label {
                 margin-bottom: 0; /* Elimina margen debajo de la etiqueta en móvil */
            }
        }
    </style>
</head>

<body>
    <?php
    // Incluir el sidebar según el rol
    if ($user_role_is_admin) {
        include "../../SIDEBAR/Admin/sidebar.php";
    } else {
        include "../../SIDEBAR/Docente/sidebar.php";
    }
    ?>
    <section class="home">
        <div class="header">
            <h1 id="titulo1-header"><?php echo ($user_role_is_admin ? 'ADMIN' : 'DOCENTE'); ?> - DETALLES DE TAREA</h1>
            <?php include '../../PHP/user_info.php'; ?>
        </div>
        <div class="main-content">
            <div class="task-detail-container">
                <?php if ($success_message): // Mostrar mensaje de éxito de la actualización de estado ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_message): // Mostrar mensaje de error ?>
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

                    <?php if ($user_role_is_admin || $current_user_id === $task_details['id_usuario']): // Solo muestra el formulario si es admin o el usuario asignado ?>
                        <form action="TareasDetalles.php?id_tarea=<?php echo htmlspecialchars($task_details['id_tarea']); ?>" method="post" class="status-update-form">
                            <input type="hidden" name="task_id" value="<?php echo htmlspecialchars($task_details['id_tarea']); ?>">
                            <label for="new_status">Cambiar Estado:</label>
                            <select id="new_status" name="new_status">
                                <?php
                                // Define los estados posibles según tu base de datos
                                $statuses = ['Pendiente', 'Completada', 'Cancelada'];
                                foreach ($statuses as $status) {
                                    $selected = ($task_details['estado_tarea'] === $status) ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($status) . "' " . $selected . ">" . htmlspecialchars($status) . "</option>";
                                }
                                ?>
                            </select>
                            <button type="submit">Actualizar Estado</button>
                        </form>
                    <?php endif; ?>

                    <a href="index.php" class="btn-volver">Volver a la Lista de Tareas</a>

                <?php else: // Mostrar mensajes de error si no se encontraron detalles ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                    <?php else: ?>
                         <div class="alert alert-info">Cargando detalles de la tarea... o tarea no encontrada.</div>
                    <?php endif; ?>
                    <a href="index.php" class="btn-volver">Volver a la Lista de Tareas</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>

</html>