<?php
session_start();
// Redirigir si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php'); // Ajusta la ruta
    exit();
}

// 1. INCLUIR CONEXIÓN (YA LO TIENES)
require_once(__DIR__ . '/../../conexion.php');
$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}
// 2. INCLUIR LA FUNCIÓN PARA CREAR NOTIFICACIONES
// Esta ruta parece ser la correcta según el error anterior que solucionamos.
// (Significa que desde 'C:\xampp\htdocs\C-Edu\Docente\Tareas asignadas\' sube dos niveles a 'C:\xampp\htdocs\C-Edu\'
// y luego entra a 'PHP/api/crear_notificacion.php')
require_once(__DIR__ . '/../../PHP/api/crear_notificacion.php');

$mensaje = ''; // Variable para mostrar mensajes de éxito o error
$stmt_insert = null; // Inicializar statement de inserción a null

// Procesar el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario de forma segura
    $instruccion = $_POST['instruccion_tarea'] ?? '';
    $fecha_fin = $_POST['fecha_fin_tarea'] ?? '';
    $prioridad = $_POST['prioridad'] ?? '';

    // En este caso, el docente se asigna la tarea a sí mismo
    $id_usuario_asignado_para_tarea = (int) $_SESSION['id_usuario']; // El ID del usuario al que se le asigna la tarea
    $id_asignador_de_tarea = (int) $_SESSION['id_usuario'];        // El ID del usuario que crea/asigna la tarea

    // Validar que los campos obligatorios no estén vacíos
    if (empty($instruccion) || empty($fecha_fin) || empty($prioridad)) {
        $mensaje = "Error: Todos los campos son obligatorios.";
    } else {
        if ($conn) {
            // Preparar la consulta SQL para insertar la nueva tarea
            $sql_insert = "INSERT INTO tarea (instruccion_tarea, id_usuario, id_asignador, fecha_inicio_tarea, fecha_fin_tarea, estado_tarea, prioridad, porcentaje_avance) VALUES (?, ?, ?, CURDATE(), ?, 'Pendiente', ?, 0)";
            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert) {
                // Vincular parámetros a la consulta preparada
                $stmt_insert->bind_param("siiss", $instruccion, $id_usuario_asignado_para_tarea, $id_asignador_de_tarea, $fecha_fin, $prioridad);

                // Ejecutar la consulta
                if ($stmt_insert->execute()) {
                    $id_tarea_creada = $conn->insert_id; // Obtener el ID de la tarea recién creada
                    $mensaje = "Tarea creada exitosamente.";

                    // ----- ¡AQUÍ ES DONDE CREAS LA NOTIFICACIÓN! -----
                    $tipo_notificacion_param = 'nueva_tarea_personal'; // Tipo específico para auto-asignación
                    $mensaje_notif_param = "Has creado una nueva tarea para ti: " . substr($instruccion, 0, 100) . (strlen($instruccion) > 100 ? "..." : "");

                    // --- MODIFICACIÓN DEL ENLACE AQUÍ ---
                    // Asumiendo que TareasDetalles.php está en la misma carpeta que este script (CrearTareaDocente.php)
                    // que es accesible vía web en /C-Edu/Docente/Tareas asignadas/
                    $ruta_url_base_para_tareas_docente = "/C-Edu/Docente/Tareas asignadas/"; // VERIFICA ESTA RUTA URL
                    $enlace_notif_param = $ruta_url_base_para_tareas_docente . "TareasDetalles.php?id_tarea=" . $id_tarea_creada;
                    // --- FIN DE LA MODIFICACIÓN DEL ENLACE ---

                    // Llamar a la función crearNotificacion()
                    if (crearNotificacion($conn, $id_usuario_asignado_para_tarea, $tipo_notificacion_param, $mensaje_notif_param, $enlace_notif_param)) {
                        error_log("Notificación de auto-asignación creada para docente $id_usuario_asignado_para_tarea por nueva tarea $id_tarea_creada. Enlace: $enlace_notif_param");
                    } else {
                        error_log("FALLO al crear notificación de auto-asignación para docente $id_usuario_asignado_para_tarea por tarea $id_tarea_creada.");
                    }
                    // ----- FIN DE LA CREACIÓN DE LA NOTIFICACIÓN -----

                    // Opcional: Redirigir a la lista de tareas del docente después de crear
                    // header('Location: index.php');
                    // exit();
                } else {
                    $mensaje = "Error al crear la tarea: " . $stmt_insert->error;
                }
            } else {
                $mensaje = "Error al preparar la consulta de inserción: " . $conn->error;
            }
        } else {
            $mensaje = "Error: No se pudo establecer la conexión a la base de datos.";
        }
    }
}

// --- INICIO: Lógica de cierre de statement mejorada ---
if ($stmt_insert instanceof mysqli_stmt) {
    $stmt_insert->close();
}
$stmt_insert = null;
// --- FIN: Lógica de cierre de statement mejorada ---

// $conn->close();
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo $theme_class;?>">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCENTE - Crear Tarea</title>
    <?php
    include __DIR__ . "/../../SIDEBAR/Docente/head.php";
    ?>
    <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos específicos para el formulario de creación de tarea ... (sin cambios) */
        .create-task-container {
            background-color: var(--bg-content);
            padding: 25px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .create-task-container h2 {
            color: var(--text-primary);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--bg-input);
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-primary);
        }

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            color: var(--text-primary);
            background-color: var(--border-color);
        }

        body.dark .form-group input[type="text"],
        body.dark .form-group input[type="date"],
        body.dark .form-group select,
        body.dark .form-group textarea {
            background-color: var(--bg-input);
            border-color: #555;
            color: var(--text-primary);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-actions {
            margin-top: 20px;
            text-align: right;
        }

        .btn-submit-task {
            padding: 10px 20px;
            background: var(--role-primary-color);
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .btn-submit-task:hover {
            background-color: var(--role-primary-dark-color);
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #ccc;
            color: var(--title-color);
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
            transition: background-color 0.3s ease;
        }

        .btn-cancel:hover {
            background-color: #bbb;
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
    </style>
</head>

<body>
    <?php
    include __DIR__ . "/../../SIDEBAR/Docente/sidebar.php";
    ?>
    <section class="home">
        <div class="main-content">
            <div class="header">
                <h1 id="titulo1-header">DOCENTE - CREAR NUEVA TAREA</h1>
                <?php
                include __DIR__ . '/../../PHP/user_info.php';
                ?>
            </div>
            <div class="create-task-container">
                <h2>Ingresar Detalles de la Tarea</h2>
                <?php if ($mensaje): ?>
                    <div
                        class="alert <?php echo (strpos($mensaje, 'Error') !== false || strpos($mensaje, 'obligatorios') !== false) ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                <form action="CrearTareaDocente.php" method="post">
                    <div class="form-group">
                        <label for="instruccion_tarea">Instrucción de la Tarea:</label>
                        <textarea id="instruccion_tarea" name="instruccion_tarea" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_tarea">Fecha de Fin:</label>
                        <input type="date" id="fecha_fin_tarea" name="fecha_fin_tarea" required>
                    </div>
                    <div class="form-group">
                        <label for="prioridad">Prioridad:</label>
                        <select id="prioridad" name="prioridad" required>
                            <option value="Baja">Baja</option>
                            <option value="Media" selected>Media</option>
                            <option value="Alta">Alta</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit-task">Crear Tarea</button>
                        <a href="index.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</body>

</html>