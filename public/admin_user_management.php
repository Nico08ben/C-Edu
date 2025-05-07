<?php
session_start();

// Verificar si el usuario está logueado y es un administrador
// Ajusta la condición de $_SESSION['rol'] si tienes un ID específico para admin, ej. $_SESSION['rol'] == 2
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 1) { // Asumiendo que rol 1 es Docente, cualquier otro es Admin
    // Si no está logueado o no es admin, redirigir a la página de login
    header("Location: index.php");
    exit;
}

$page_title = "Gestión de Usuarios - Administrador"; // Título base para esta página

// Determinar la acción solicitada, por defecto será 'list' para mostrar la lista de usuarios
$action = $_GET['action'] ?? 'list';
$user_id = $_GET['id_usuario'] ?? null; // Para acciones como edit, delete, etc.

// Construir la ruta base a las vistas/manejadores del módulo de gestión de usuarios
$base_module_path = __DIR__ . '/../src/modules/user_management/';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
    require_once __DIR__ . '/../src/includes/admin_head.php'; 
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="assets/css/user_management.css">
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
</head>
<body>
    <?php 
    require_once __DIR__ . '/../src/includes/user_header.php'; 
    ?>

    <div class="main-container">
        <?php 
        require_once __DIR__ . '/../src/includes/admin_sidebar.php'; 
        ?>

        <main class="content">
            <?php
            // Enrutador simple para cargar la vista o el manejador apropiado
            switch ($action) {
                case 'list': // Acción por defecto
                    require_once $base_module_path . 'index.php'; // Antes usuarios.php (lista de usuarios)
                    break;
                case 'create_form': // Mostrar formulario de creación
                    $page_title = "Crear Nuevo Usuario - Administrador"; // Actualizar título
                    require_once $base_module_path . 'user_form_view.php'; // Antes Edit_User/index.php (para creación)
                    break;
                case 'store_user': // Procesar creación de usuario
                    // La lógica de creación está en create_user_handler.php (antes procesar_usuario.php)
                    // Este handler podría redirigir o mostrar un mensaje.
                    require_once $base_module_path . 'create_user_handler.php';
                    break;
                case 'edit_form': // Mostrar formulario de edición
                    if ($user_id) {
                        $page_title = "Editar Usuario - Administrador"; // Actualizar título
                        // user_form_view.php necesitará el $user_id para cargar datos
                        $_GET['id_usuario'] = $user_id; // Asegurar que id_usuario esté disponible para la vista
                        require_once $base_module_path . 'user_form_view.php'; // Antes Edit_User/index.php (para edición)
                    } else {
                        echo "<p>Error: ID de usuario no especificado para la edición.</p>";
                        echo '<a href="admin_user_management.php">Volver a la lista</a>';
                    }
                    break;
                case 'update_user': // Procesar actualización de usuario
                    // La lógica de actualización está en update_user_handler.php (antes actualizar_usuario.php)
                    require_once $base_module_path . 'update_user_handler.php';
                    break;
                case 'change_password': // Procesar cambio de contraseña
                     // La lógica de cambio de contraseña está en change_password_handler.php (antes cambiar_password.php)
                    require_once $base_module_path . 'change_password_handler.php';
                    break;
                case 'delete_user': // Procesar eliminación de usuario
                    // La lógica de eliminación está en delete_user_handler.php (antes eliminar_usuario.php)
                    require_once $base_module_path . 'delete_user_handler.php';
                    break;
                // El archivo get_subjects_ajax.php (antes obtener_materias.php) se llamaría directamente por AJAX
                // o se incluiría donde sea necesario si es una función de PHP.
                // Si es AJAX, necesitará su propio punto de entrada público o una forma de ser enrutado.
                // Por ahora no lo incluimos en este switch principal.

                default:
                    echo "<p>Acción no reconocida.</p>";
                    require_once $base_module_path . 'index.php'; // Lista de usuarios por defecto
                    break;
            }
            ?>
        </main>
    </div>
    <script src="assets/js/user_management.js"></script>
</body>
</html>