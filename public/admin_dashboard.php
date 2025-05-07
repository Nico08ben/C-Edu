<?php
session_start();

// Verificar si el usuario está logueado y es un administrador (asumiendo que rol ID != 1 es Admin)
// Ajusta la condición de $_SESSION['rol'] si tienes un ID específico para admin, ej. $_SESSION['rol'] == 2
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 1) { 
    // Si no está logueado o no es admin, redirigir a la página de login
    header("Location: index.php");
    exit;
}

// El ID del usuario y su rol están en la sesión:
$id_usuario_actual = $_SESSION['id_usuario'];
$rol_usuario_actual = $_SESSION['rol'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
    // Incluimos la cabecera específica del administrador
    require_once __DIR__ . '/../src/includes/admin_head.php'; 
    ?>
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="assets/css/admin_dashboard_styles.css">
    <title>Dashboard Administrador</title> <?php // O puedes poner el título en admin_head.php ?>
</head>
<body>
<?php
    // Incluimos la barra lateral específica del docente
    require_once __DIR__ . '/../src/includes/admin_sidebar.php';
    ?>
    <section class="home">
        <main class="main-content">
            <div class="header">
                <h1 id="titulo1-header">Bienvenido a C-EDU</h1>
                <?php
                // Incluimos el header común de información de usuario
                require_once __DIR__ . '/../src/includes/user_header.php';
                ?>
            </div>
            <?php
            // Incluimos la vista principal del dashboard del docente
            // Esta era originalmente Docente/Home/index.php
            require_once __DIR__ . '/../src/modules/admin_dashboard/index_view.php';
            ?>
        </main>
    </section>
</body>
</html>