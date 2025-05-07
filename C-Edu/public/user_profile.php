<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
    // Si no está logueado o no tiene rol, redirigir a la página de login
    header("Location: index.php");
    exit;
}

$id_usuario_actual = $_SESSION['id_usuario'];
$rol_usuario_actual = $_SESSION['rol']; // Asumiendo 1 para Docente, y otro número (ej. 2) para Admin

// Determinar qué archivos de cabecera, sidebar y vista de perfil cargar
$head_file = '';
$sidebar_file = '';
$profile_view_file = '';

if ($rol_usuario_actual == 1) { // Docente
    $head_file = __DIR__ . '/../src/includes/docente_head.php';
    $sidebar_file = __DIR__ . '/../src/includes/docente_sidebar.php';
    $profile_view_file = __DIR__ . '/../src/modules/user_profile/docente_profile_view.php';
    $page_title = "Perfil del Docente";
} else { // Administrador (asumiendo cualquier rol que no sea 1 es admin, ajusta si tienes más roles)
    $head_file = __DIR__ . '/../src/includes/admin_head.php';
    $sidebar_file = __DIR__ . '/../src/includes/admin_sidebar.php';
    $profile_view_file = __DIR__ . '/../src/modules/user_profile/admin_profile_view.php';
    $page_title = "Perfil del Administrador";
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
    if (file_exists($head_file)) {
        require_once $head_file; 
    } else {
        // Fallback básico si el archivo head específico del rol no existe
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    }
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <?php // Recordatorio: Asegúrate que user_header.css se enlaza en los *_head.php ?>
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
</head>
<body>
    <?php 
    // Incluimos el header común de información de usuario
    require_once __DIR__ . '/../src/includes/user_header.php'; 
    ?>

    <div class="main-container"> <?php // Un contenedor principal para el sidebar y el contenido ?>
        <?php 
        // Incluimos la barra lateral específica del rol
        if (file_exists($sidebar_file)) {
            require_once $sidebar_file; 
        }
        ?>

        <main class="content">
            <?php 
            // Incluimos la vista de perfil específica del rol
            // Estas eran originalmente Administrador/UserProfile/index.php y Docente/UserProfile/index.php
            if (file_exists($profile_view_file)) {
                require_once $profile_view_file; 
            } else {
                echo "<p>Error: No se encontró la vista de perfil.</p>";
            }
            ?>
        </main>
    </div>

    <?php // Si tuvieras un pie de página común o scripts JS comunes al final del body: ?>
    <?php // require_once __DIR__ . '/../src/includes/main_footer.php'; ?>
</body>
</html>