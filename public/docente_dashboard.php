<?php
session_start();

// Verificar si el usuario está logueado y es un docente (asumiendo que rol ID 1 es Docente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    // Si no está logueado o no es docente, redirigir a la página de login
    header("Location: index.php");
    exit;
}

// El ID del usuario y su rol están en la sesión:
// $id_usuario_actual = $_SESSION['id_usuario'];
// $rol_usuario_actual = $_SESSION['rol'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="assets/css/docente_sidebar.css">
    <link rel="stylesheet" href="assets/css/docente_dashboard.css">
    <?php 
    // Incluimos la cabecera específica del docente (CSS, favicons, título, etc.)
    // La ruta es relativa desde public/docente_dashboard.php
    require_once __DIR__ . '/../src/includes/docente_head.php'; 
    ?>
    <title>Dashboard Docente</title> <?php // O puedes poner el título en docente_head.php ?>
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
            // Incluimos la vista principal del dashboard del docente
            // Esta era originalmente Docente/Home/index.php
            require_once __DIR__ . '/../src/modules/docente_dashboard/index_view.php'; 
            ?>
        </main>
    </div>

    <?php // Si tuvieras un pie de página común o scripts JS comunes al final del body: ?>
    <?php // require_once __DIR__ . '/../src/includes/main_footer.php'; ?>
    <?php // <script src="assets/js/common_scripts.js"></script> ?>
</body>
</html>