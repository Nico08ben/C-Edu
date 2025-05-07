<?php
session_start(); // Debe ser lo primero

// Verificar si el usuario está logueado y es un admin
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 1) {
    header("Location: index.php"); // Redirección antes de cualquier salida
    exit;
}

$action = $_GET['action'] ?? 'list';
$base_view_path = __DIR__ . '/../src/modules/admin_tasks/';
$current_task_id = $_GET['id_tarea'] ?? $_POST['task_id'] ?? null; // Necesitamos el ID de tarea para detalles/update

// --- MANEJO DE ACCIONES QUE PUEDEN REDIRIGIR (ANTES DE CUALQUIER HTML) ---
if ($action === 'store_task' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // create_task_view.php se encarga de su POST y redirige/sale si es necesario.
    require_once $base_view_path . 'create_task_view.php'; // Ejecuta el bloque POST. Si redirige, el script se detiene.
    // Si fue POST y falló (no redirigió), preparamos para mostrar el formulario de creación con error.
    $action = 'create';
} elseif ($action === 'update_status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // task_details_view.php ahora maneja su POST y redirige/sale.
    require_once $base_view_path . 'task_details_view.php'; // Ejecuta el bloque POST. Si redirige, el script se detiene.
    // Si por alguna razón no redirigiera (aunque debería con PRG), la lógica abajo mostraría detalles.
    // La redirección dentro de task_details_view.php se encargará de volver a action=details.
    // No necesitamos cambiar $action aquí porque la redirección lo hará.
}


// --- DETERMINAR TÍTULO Y VISTA PARA MOSTRAR (SI NO HUBO REDIRECCIÓN O ES GET) ---
$page_title_for_display = "Gestión de Tareas"; // Título por defecto
$view_file_to_include = '';

switch ($action) {
    case 'create':
        $page_title_for_display = "Crear Nueva Tarea - admin";
        $view_file_to_include = $base_view_path . 'create_task_view.php';
        break;
    case 'details':
        $page_title_for_display = "Detalles de Tarea - admin";
        $view_file_to_include = $base_view_path . 'task_details_view.php';
        break;
    case 'list':
    default:
        $page_title_for_display = "Gestión de Tareas - admin";
        $view_file_to_include = $base_view_path . 'index_view.php';
        break;
}

// Verificar si el archivo de vista existe antes de continuar
if (empty($view_file_to_include) || !file_exists($view_file_to_include)) {
    // Podrías querer mostrar un error 404 aquí o redirigir a una página de error.
    // Por ahora, si la acción no coincide, podría caer en el 'default' de arriba.
    // Si $view_file_to_include sigue vacío o no existe, el 'require_once' de abajo fallará.
    // Añadimos una comprobación más robusta:
    if (empty($view_file_to_include) && $action !== 'list') { // Si no es list (que tiene default) y no se encontró vista
        $page_title_for_display = "Error - Acción no válida";
        // $view_file_to_include = $base_view_path . 'error_view.php'; // Si tienes una vista de error genérica
    } elseif (!file_exists($view_file_to_include)) {
        $page_title_for_display = "Error - Vista no encontrada";
        // $view_file_to_include = $base_view_path . 'error_view.php';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../src/includes/admin_head.php'; ?>
    <title><?= htmlspecialchars($page_title_for_display) ?></title>
    <link rel="stylesheet" href="assets/css/admin_tasks.css">
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
</head>
<body>
    <?php require_once __DIR__ . '/../src/includes/admin_sidebar.php'; ?>
    <section class="home">
        <div class="main-content">
            <div class="header">
                <h1 id="titulo1-header"><?= htmlspecialchars($page_title_for_display) ?></h1>
                <?php require_once __DIR__ . '/../src/includes/user_header.php'; ?>
            </div>
        </div>

        <?php
        // Incluir el contenido principal de la vista
        if (!empty($view_file_to_include) && file_exists($view_file_to_include)) {
            require_once $view_file_to_include;
        } else {
            echo "<div style='padding: 20px; text-align: center; color: red;'>Error: Contenido de la vista no pudo ser cargado. Acción: '" . htmlspecialchars($action) . "'.</div>";
        }
        ?>
    </section> <?php // Revisa si tus vistas cierran esto ?>
    <script src="assets/js/admin_tasks.js"></script>
</body>  <?php // Revisa si tus vistas cierran esto ?>
</html> <?php // Revisa si tus vistas cierran esto ?>