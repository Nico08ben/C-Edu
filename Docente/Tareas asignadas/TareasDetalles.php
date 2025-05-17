<?php
session_start();
// Redirigir si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php'); // Ajusta la ruta
    exit();
}

// Incluir el archivo de conexión a la base de datos
require_once(__DIR__ . '/../../conexion.php');
$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}
$task_details = null;
$error_message = '';
$success_message = ''; // Variable para mensajes de éxito
$stmt = null; // Inicializar statement a null
$result = null; // Inicializar resultado a null

$current_user_id = $_SESSION['id_usuario']; // Obtener el ID del docente actual

// --- INICIO: Lógica para cambiar el estado de la tarea ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'], $_POST['new_status'])) {
    $task_id_to_update = $_POST['task_id'];
    $new_status = $_POST['new_status'];

    // Validar que el nuevo estado sea uno permitido según la base de datos
    $allowed_statuses = ['Pendiente', 'Completada', 'Cancelada']; // <-- AJUSTADO según tu base de datos
    if (!in_array($new_status, $allowed_statuses)) {
        $error_message = "Estado de tarea no válido.";
    } else {
        if ($conn) {
            // Consulta para actualizar el estado, PERO SOLO si la tarea está asignada al docente actual
            $sql_update = "UPDATE tarea SET estado_tarea = ? WHERE id_tarea = ? AND id_usuario = ?";
            $stmt_update = $conn->prepare($sql_update);

            if ($stmt_update) {
                $stmt_update->bind_param("sii", $new_status, $task_id_to_update, $current_user_id);
                if ($stmt_update->execute()) {
                    // Verificar si alguna fila fue afectada (si la tarea existía y estaba asignada al usuario)
                    if ($stmt_update->affected_rows > 0) {
                        $success_message = "Estado de la tarea actualizado exitosamente a '" . htmlspecialchars($new_status) . "'.";
                        // No redirigimos para que el usuario vea el mensaje y el estado actualizado en la misma página
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
// Obtener el ID de la tarea de la URL (si se accede por GET) o del POST (después de una actualización)
$id_tarea = $_GET['id_tarea'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $id_tarea = $_POST['task_id'];
}


if ($id_tarea && !empty($id_tarea)) {

    if ($conn) {
        // Consulta para obtener detalles de la tarea, nombre del asignado y nombre del creador
        // PERO SOLO si la tarea está asignada al docente actual
        $sql = "SELECT t.*,
                        u_asignado.nombre_usuario AS nombre_asignado,
                        u_creador.nombre_usuario AS nombre_creador
                FROM tarea t
                INNER JOIN usuario u_asignado ON t.id_usuario = u_asignado.id_usuario
                INNER JOIN usuario u_creador ON t.id_asignador = u_creador.id_usuario
                WHERE t.id_tarea = ? AND t.id_usuario = ?"; // Filtra por ID de tarea Y por el usuario asignado

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ii", $id_tarea, $current_user_id); // Bind ID de tarea y ID del usuario actual
            if ($stmt->execute()) {
                 $result = $stmt->get_result();

                if ($result && $result->num_rows > 0) {
                    $task_details = $result->fetch_assoc();
                } else {
                    // Si no se encuentran detalles, establecer un mensaje de error
                    $error_message = "No se encontraron detalles para la tarea solicitada o no está asignada a ti.";
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
<!DOCTYPE html>
<html lang="es" class="<?php echo $theme_class;?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCENTE - Detalles de Tarea</title> <?php include "../../SIDEBAR/Docente/head.php"; ?> <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos adicionales para la página de detalles */
        .task-detail-container {
            background-color: var(--sidebar-color); /* Usando color de sidebar */
            padding: 30px; /* Aumentar padding */
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Sombra más pronunciada */
            max-width: 800px; /* Limitar ancho máximo */
            margin-left: auto; /* Centrar contenedor */
            margin-right: auto; /* Centrar contenedor */
        }

        .task-detail-container h2 {
            color: var(--primary-color);
            margin-bottom: 25px; /* Aumentar margen */
            border-bottom: 2px solid var(--primary-color-ligth); /* Usando la variable light */
            padding-bottom: 15px; /* Aumentar padding */
            font-size: 1.8rem; /* Aumentar tamaño de fuente */
        }

        .task-detail-item {
            margin-bottom: 20px; /* Aumentar margen */
            font-size: 1.1rem; /* Aumentar tamaño de fuente */
            color: var(--text-color); /* Usando la variable de texto */
            display: flex; /* Usar flexbox para alinear label y valor */
            align-items: flex-start; /* Alinear al inicio */
        }

        .task-detail-item strong {
            color: var(--title-color); /* Usando la variable de título */
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
            color: var(--title-color);
        }

        .status-update-form select {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: var(--body-color); /* Fondo del select */
            color: var(--text-color); /* Color de texto del select */
            cursor: pointer;
            outline: none;
            transition: border-color 0.3s ease;
        }

         body.dark .status-update-form select {
             background-color: var(--primary-color-ligth); /* Fondo oscuro para select en modo oscuro */
             border-color: #555; /* Borde más oscuro en modo oscuro */
             color: var(--text-color); /* Color de texto en modo oscuro */
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
            background-color: #35a88d; /* Tono más oscuro para hover (ajustado al nuevo color) */
        }

        .btn-volver {
            display: inline-block; /* Asegurar que padding y margin funcionen */
            padding: 10px 20px;
            background: var(--primary-color); /* Usando la variable primary */
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
    <?php include "../../SIDEBAR/Docente/sidebar.php"; ?> <section class="home">
    <div class="header">
                <h1 id="titulo1-header">DOCENTE - DETALLES DE TAREA</h1> <?php include '../../PHP/user_info.php'; // Reutilizando user_info.php ?>
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

                    <form action="TareasDetalles.php?id_tarea=<?php echo htmlspecialchars($task_details['id_tarea']); ?>" method="post" class="status-update-form">
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
