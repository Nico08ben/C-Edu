<?php
session_start();

// 1. INCLUIR CONEXIÓN
require_once(__DIR__ . '/../../conexion.php'); // Sube dos niveles desde Administrador/Tareas asignadas/ hasta C-Edu/

// 2. INCLUIR LA FUNCIÓN PARA CREAR NOTIFICACIONES
// Si CrearTarea.php está en C:\xampp\htdocs\C-Edu\Administrador\Tareas asignadas\
// y crear_notificacion.php está en C:\xampp\htdocs\C-Edu\PHP\api\
// Subir dos niveles (desde Administrador/Tareas asignadas/ a C-Edu/) y luego entrar a PHP/api/
require_once(__DIR__ . '/../../PHP/api/crear_notificacion.php');

$mensaje = ''; // Variable para mostrar mensajes de éxito o error
$stmt_insert = null; // Inicializar statement de inserción a null
$result_users = null; // Inicializar resultado de usuarios a null

// Procesar el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario de forma segura
    $instruccion = $_POST['instruccion_tarea'] ?? '';
    $id_usuario_asignado = $_POST['id_usuario_asignado'] ?? ''; // El ID del usuario al que el Admin asigna la tarea
    $fecha_fin = $_POST['fecha_fin_tarea'] ?? '';
    $prioridad = $_POST['prioridad'] ?? '';

    $id_asignador = (int)$_SESSION['id_usuario']; // El administrador actual es quien asigna la tarea

    // Validar que los campos obligatorios no estén vacíos
    if (empty($instruccion) || empty($id_usuario_asignado) || empty($fecha_fin) || empty($prioridad)) {
        $mensaje = "Error: Todos los campos son obligatorios.";
    } else {
        // Convertir $id_usuario_asignado a entero para la notificación y la inserción
        $id_usuario_asignado_int = (int)$id_usuario_asignado;

        if ($conn) {
            // Preparar la consulta SQL para insertar la nueva tarea
            $sql_insert = "INSERT INTO tarea (instruccion_tarea, id_usuario, id_asignador, fecha_inicio_tarea, fecha_fin_tarea, estado_tarea, prioridad, porcentaje_avance) VALUES (?, ?, ?, CURDATE(), ?, 'Pendiente', ?, 0)";
            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert) {
                // Vincular parámetros a la consulta preparada
                $stmt_insert->bind_param("siiss", $instruccion, $id_usuario_asignado_int, $id_asignador, $fecha_fin, $prioridad);

                // Ejecutar la consulta
                if ($stmt_insert->execute()) {
                    $id_tarea_creada = $conn->insert_id; // Obtener el ID de la tarea recién creada
                    $mensaje = "Tarea creada y asignada exitosamente.";

                    // ----- ¡AQUÍ ES DONDE CREAS LA NOTIFICACIÓN PARA EL USUARIO ASIGNADO! -----
                    $tipo_notificacion_param = 'nueva_tarea_asignada';
                    $nombre_admin_asignador = $_SESSION['nombre_usuario'] ?? 'Un administrador'; // Asume que tienes 'nombre_usuario' en sesión

                    $mensaje_notif_param = $nombre_admin_asignador . " te ha asignado una nueva tarea: " . substr($instruccion, 0, 80) . (strlen($instruccion) > 80 ? "..." : "");

                    // --- MODIFICACIÓN DEL ENLACE AQUÍ ---
                    // Asumiendo que TareasDetalles.php (el script que ve el DOCENTE) se usará para ver los detalles.
                    // La ruta URL a la carpeta que contiene TareasDetalles.php es /C-Edu/Docente/Tareas asignadas/
                    $ruta_url_a_detalles_de_tarea_docente = '/C-Edu/Docente/Tareas asignadas/'; // RUTA A LA CARPETA DE TAREASDETALLES.PHP
                    $enlace_notif_param = $ruta_url_a_detalles_de_tarea_docente . "TareasDetalles.php?id_tarea=" . $id_tarea_creada;
                    // --- FIN DE LA MODIFICACIÓN DEL ENLACE ---


                    // Llamar a la función crearNotificacion() para el USUARIO ASIGNADO
                    if (crearNotificacion($conn, $id_usuario_asignado_int, $tipo_notificacion_param, $mensaje_notif_param, $enlace_notif_param)) {
                        error_log("Notificación creada para usuario $id_usuario_asignado_int por tarea $id_tarea_creada asignada por admin $id_asignador. Enlace: $enlace_notif_param");
                    } else {
                        error_log("FALLO al crear notificación para usuario $id_usuario_asignado_int por tarea $id_tarea_creada.");
                    }
                    // ----- FIN DE LA CREACIÓN DE LA NOTIFICACIÓN -----

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

// Obtener la lista de usuarios para el selector en el formulario (sin cambios)
$users = [];
if ($conn) {
    $sql_users = "SELECT id_usuario, nombre_usuario FROM usuario ORDER BY nombre_usuario ASC";
    $result_users = $conn->query($sql_users);
    if ($result_users) {
        if ($result_users->num_rows > 0) {
            while ($row_user = $result_users->fetch_assoc()) {
                $users[] = $row_user;
            }
        }
        $result_users->free();
    } else {
         $mensaje .= (empty($mensaje) ? "" : "<br>") . "Error al obtener la lista de usuarios: " . $conn->error;
    }
}

if ($stmt_insert instanceof mysqli_stmt) {
    $stmt_insert->close();
}
$stmt_insert = null;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADMIN - Crear Tarea</title>
    <?php include "../../SIDEBAR/Admin/head.php"; // Ruta desde Administrador/Tareas asignadas/ -> ../../SIDEBAR/Admin/head.php ?>
    <link rel="stylesheet" href="tareascss.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos CSS (sin cambios) */
        .create-task-container { background-color: var(--sidebar-color); padding: 25px; margin-top: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); max-width: 600px; margin-left: auto; margin-right: auto; }
        .create-task-container h2 { color: var(--primary-color); margin-bottom: 20px; border-bottom: 2px solid var(--primary-color-ligth); padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; color: var(--text-color); }
        .form-group input[type="text"], .form-group input[type="date"], .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; color: var(--text-color); background-color: var(--body-color); }
        body.dark .form-group input[type="text"], body.dark .form-group input[type="date"], body.dark .form-group textarea { background-color: var(--primary-color-ligth); border-color: #555; color: var(--text-color); }
        .form-group textarea { resize: vertical; min-height: 100px; }
        .form-actions { margin-top: 20px; text-align: right; }
        .btn-submit-task { padding: 10px 20px; background: var(--primary-color); color: white; border: none; font-size: 16px; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; transition: background-color 0.3s ease; }
        .btn-submit-task:hover { background-color: #d4a738; }
        .btn-cancel { padding: 10px 20px; background: #ccc; color: var(--title-color); border: none; font-size: 16px; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin-left: 10px; transition: background-color 0.3s ease; }
        .btn-cancel:hover { background-color: #bbb; }
        .alert-success { color: #155724; background-color: #d4edda; border-color: #c3e6cb; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: .25rem; }
        .alert-danger { color: #721c24; background-color: #f8d7da; border-color: #f5c6cb; padding: 15px; margin-bottom: 20px; border: 1px solid transparent; border-radius: .25rem; }
        .select2-container--default .select2-selection--single { height: 38px; border: 1px solid #ccc; border-radius: 4px; padding: 6px 12px; background-color: var(--body-color); }
        .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 24px; color: var(--text-color); }
        .select2-container--default .select2-selection--single .select2-selection__arrow { height: 36px; }
        .select2-dropdown { border: 1px solid #ccc; border-radius: 4px; background-color: var(--sidebar-color); }
        .select2-results__option { padding: 8px 12px; color: var(--text-color); }
        .select2-results__option--highlighted[aria-selected] { background-color: var(--primary-color-ligth); color: var(--title-color); }
        .select2-search--dropdown .select2-search__field { border: 1px solid #ccc; padding: 8px; background-color: var(--body-color); color: var(--text-color); }
        body.dark .select2-container--default .select2-selection--single { background-color: var(--primary-color-ligth); border-color: #555; }
        body.dark .select2-container--default .select2-selection--single .select2-selection__rendered { color: var(--text-color); }
        body.dark .select2-dropdown { background-color: var(--sidebar-color); border-color: #555; }
        body.dark .select2-results__option { color: var(--text-color); }
        body.dark .select2-results__option--highlighted[aria-selected] { background-color: var(--primary-color); color: white; }
        body.dark .select2-search--dropdown .select2-search__field { background-color: var(--body-color); color: var(--text-color); border-color: #555; }
        @media (max-width: 768px) { .create-task-container { padding: 15px; margin-top: 15px; } .create-task-container h2 { font-size: 1.3rem; margin-bottom: 15px; padding-bottom: 8px; } .form-group { margin-bottom: 10px; } .form-group label { font-size: 0.9rem; margin-bottom: 3px; } .form-group input[type="text"], .form-group input[type="date"], .form-group textarea { padding: 6px; font-size: 0.9rem; } .select2-container--default .select2-selection--single { height: 32px; padding: 4px 8px; } .select2-container--default .select2-selection--single .select2-selection__rendered { line-height: 24px; } .select2-container--default .select2-selection--single .select2-selection__arrow { height: 30px; } .form-actions { text-align: center; } .btn-submit-task, .btn-cancel { width: auto; margin-left: 5px; margin-right: 5px; } }
    </style>
</head>

<body>
    <?php include "../../SIDEBAR/Admin/sidebar.php"; // Ruta desde Administrador/Tareas asignadas/ ?>
    <section class="home">
        <div class="main-content">
            <div class="header">
                <h1 id="titulo1-header">ADMIN - CREAR NUEVA TAREA</h1>
                <?php include '../../PHP/user_info.php'; // Ruta desde Administrador/Tareas asignadas/ -> ../../PHP/user_info.php ?>
            </div>

            <div class="create-task-container">
                <h2>Ingresar Detalles de la Tarea</h2>
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo (strpos($mensaje, 'Error') !== false || strpos($mensaje, 'obligatorios') !== false) ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                <form action="CrearTarea.php" method="post">
                    <div class="form-group">
                        <label for="instruccion_tarea">Instrucción de la Tarea:</label>
                        <textarea id="instruccion_tarea" name="instruccion_tarea" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="id_usuario_asignado">Asignar a Usuario:</label>
                        <select id="id_usuario_asignado" name="id_usuario_asignado" required style="width: 100%;">
                            <option value="">-- Seleccione un usuario --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                                    <?php echo htmlspecialchars($user['nombre_usuario']); ?></option>
                            <?php endforeach; ?>
                        </select>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/es.js"></script>

    <script>
        $(document).ready(function() {
            $('#id_usuario_asignado').select2({
                placeholder: "-- Seleccione un usuario --",
                allowClear: true,
                language: "es"
            });
        });
    </script>
</body>
</html>