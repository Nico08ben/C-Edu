<?php
session_start();

// Verificar si el usuario está logueado y es un docente (asumiendo que rol ID 1 es Docente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    // Si no está logueado o no es docente, redirigir a la página de login
    header("Location: index.php");
    exit;
}

$page_title = "Gestión de Tareas - Docente"; // Título para esta página

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
    // Incluimos la cabecera específica del docente
    require_once __DIR__ . '/../src/includes/docente_head.php'; 
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
        <link rel="stylesheet" href="assets/css/docente_tasks.css"> 
        <link rel="stylesheet" href="assets/css/docente_sidebar.css">
</head>
<body>
    <?php 
    // Incluimos el header común de información de usuario
    require_once __DIR__ . '/../src/includes/user_header.php'; 
    ?>

    <div class="main-container"> <?php // Un contenedor principal para el sidebar y el contenido ?>
        <?php 
        // Incluimos la barra lateral específica del docente
        require_once __DIR__ . '/../src/includes/docente_sidebar.php'; 
        ?>

        <main class="content">
        <?php
            // Determinar la acción solicitada, por defecto será 'list' para mostrar el index_view.php
            $action = $_GET['action'] ?? 'list'; // Usamos 'list' como acción por defecto

            // Construir la ruta base a las vistas del módulo de tareas del docente
            $base_view_path = __DIR__ . '/../src/modules/docente_tasks/';

            // Enrutador simple para cargar la vista o el manejador apropiado
            switch ($action) {
                case 'create':
                    // Muestra el formulario para crear una nueva tarea (docente se la asigna a sí mismo)
                    require_once $base_view_path . 'create_task_view.php';
                    break;
                case 'store_task':
                    // Procesa el envío del formulario de creación de tarea.
                    // El archivo create_task_view.php contiene la lógica para manejar el POST.
                    require_once $base_view_path . 'create_task_view.php';
                    break;
                case 'details':
                    // Muestra los detalles de una tarea específica asignada al docente.
                    // El archivo task_details_view.php obtiene el id_tarea de $_GET.
                    require_once $base_view_path . 'task_details_view.php';
                    break;
                case 'update_status':
                    // Procesa la actualización del estado de una tarea por parte del docente.
                    // El archivo task_details_view.php contiene la lógica para manejar el POST.
                    require_once $base_view_path . 'task_details_view.php';
                    break;
                case 'list': // Acción por defecto o si explícitamente se pide la lista
                default:
                    // Muestra la lista principal de tareas del docente
                    require_once $base_view_path . 'index_view.php';
                    break;
            }
            ?>
        </main>
    </div>

        <script src="assets/js/docente_tasks.js"></script>
    
</body>
</html>