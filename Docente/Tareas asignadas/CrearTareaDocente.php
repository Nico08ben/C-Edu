<?php
session_start();
// Redirigir si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php'); // Ajusta la ruta
    exit();
}

// 1. INCLUIR CONEXIÓN (YA LO TIENES)
require_once(__DIR__ . '/../../conexion.php');

// 2. INCLUIR LA FUNCIÓN PARA CREAR NOTIFICACIONES
//    Asegúrate que la ruta a crear_notificacion.php sea correcta.
//    Si CrearTareaDocente.php está en la raíz y la API en PHP/api/:
//    require_once(__DIR__ . '/PHP/api/crear_notificacion.php');
//    Si CrearTareaDocente.php está en una carpeta y PHP/api/ está en otra:
//    Ajusta los '../' según sea necesario. Por ejemplo, si ambos están dentro de una carpeta "Docente":
//    Asumiendo que 'PHP/api/crear_notificacion.php' es la ubicación correcta relativa a la raíz del proyecto,
//    y este script (CrearTareaDocente.php) está en una carpeta, por ejemplo, 'tareas_docente/'
//    Si 'PHP/' es una carpeta en la raíz del proyecto:
require_once(__DIR__ . '/../../PHP/api/crear_notificacion.php'); // Ajusta esta ruta cuidadosamente

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
                    // El enlace podría llevar al usuario a una página para ver los detalles de esa tarea.
                    // Ajusta este enlace según la estructura de tu sitio y cómo visualizas las tareas.
                    // Si CrearTareaDocente.php está en una carpeta "tareas_docente", y la vista de tareas está en el mismo nivel:
                    $enlace_notif_param = "ver_tarea.php?id_tarea=" . $id_tarea_creada; // Ejemplo, ajusta la ruta

                    // Llamar a la función crearNotificacion()
                    // $conn (conexión mysqli) ya está disponible.
                    // $id_usuario_asignado_para_tarea es el destinatario (el mismo docente en este caso).
                    if (crearNotificacion($conn, $id_usuario_asignado_para_tarea, $tipo_notificacion_param, $mensaje_notif_param, $enlace_notif_param)) {
                        // Notificación creada exitosamente (puedes loggear esto si quieres)
                        error_log("Notificación de auto-asignación creada para docente $id_usuario_asignado_para_tarea por nueva tarea $id_tarea_creada.");
                    } else {
                        // Hubo un error al crear la notificación (la función crearNotificacion ya loggea el error)
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

// $conn->close(); // No cierres la conexión aquí si la necesitas más abajo en el HTML o en includes.
// Generalmente se cierra al final de la ejecución del script principal o es manejada por PHP.
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCENTE - Crear Tarea</title>
    <?php
    // Ajustar la ruta para head.php. Si CrearTareaDocente.php está en la raíz:
    // include __DIR__ . "/SIDEBAR/Docente/head.php";
    // Si CrearTareaDocente.php está, por ejemplo, en una carpeta "tareas_docente/"
    // y SIDEBAR está un nivel arriba:
    include __DIR__ . "/../../SIDEBAR/Docente/head.php"; // Parece que esta es tu estructura actual
    ?>
    <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos específicos para el formulario de creación de tarea */
        .create-task-container {
            background-color: var(--sidebar-color);
            /* Usando color de sidebar para el contenedor */
            padding: 25px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            /* Limitar ancho máximo para el formulario */
            margin-left: auto;
            margin-right: auto;
        }

        .create-task-container h2 {
            color: var(--primary-color);
            /* Usando color primario para el título */
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color-ligth);
            /* Usando color primario ligero para el borde */
            padding-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-color);
            /* Usando color de texto */
        }

        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            /* Borde gris */
            border-radius: 4px;
            box-sizing: border-box;
            /* Incluir padding y border en el tamaño total */
            color: var(--text-color);
            /* Color de texto para inputs */
            background-color: var(--body-color);
            /* Fondo ligero para inputs */
        }

        body.dark .form-group input[type="text"],
        body.dark .form-group input[type="date"],
        body.dark .form-group select,
        body.dark .form-group textarea {
            background-color: var(--primary-color-ligth);
            /* Fondo oscuro para inputs en modo oscuro */
            border-color: #555;
            /* Borde más oscuro en modo oscuro */
            color: var(--text-color);
            /* Color de texto en modo oscuro */
        }

        .form-group textarea {
            resize: vertical;
            /* Permitir redimensionamiento vertical */
            min-height: 100px;
        }

        .form-actions {
            margin-top: 20px;
            text-align: right;
            /* Alinea los botones a la derecha */
        }

        .btn-submit-task {
            padding: 10px 20px;
            background: var(--primary-color);
            /* Usando color primario */
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
            background-color: #d4a738;
            /* Tono más oscuro para hover */
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #ccc;
            /* Color gris para cancelar */
            color: var(--title-color);
            /* Color de título para texto */
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
            /* Tono más oscuro para hover */
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
    // Ajustar la ruta para sidebar.php
    include __DIR__ . "/../../SIDEBAR/Docente/sidebar.php"; // Parece que esta es tu estructura actual
    ?>
    <section class="home">
        <div class="header">
            <h1 id="titulo1-header">DOCENTE - CREAR NUEVA TAREA</h1>
            <?php
            // Ajustar la ruta para user_info.php
            include __DIR__ . '/../../PHP/user_info.php'; // Si user_info.php está en la carpeta PHP/
            ?>
        </div>
        <div class="main-content">
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