<?php
session_start();

// Verificar si el usuario está logueado y es un administrador 
// Ajusta la condición de $_SESSION['rol'] si tienes un ID específico para admin, ej. $_SESSION['rol'] == 2
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 1) { // Asumiendo que rol 1 es Docente, cualquier otro es Admin
    // Si no está logueado o no es admin, redirigir a la página de login
    header("Location: index.php");
    exit;
}

$page_title = "Chat - Administrador"; // Título para esta página

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
    // Incluimos la cabecera específica del administrador
    require_once __DIR__ . '/../src/includes/admin_head.php'; 
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="assets/css/chat_admin.css">
</head>
<body>
    <?php 
    // Incluimos el header común de información de usuario
    require_once __DIR__ . '/../src/includes/user_header.php'; 
    ?>

    <div class="main-container"> <?php // Un contenedor principal para el sidebar y el contenido ?>
        <?php 
        // Incluimos la barra lateral específica del administrador
        require_once __DIR__ . '/../src/includes/admin_sidebar.php'; 
        ?>

        <main class="content">
            <?php 
            // Incluimos la vista principal del chat del administrador
            // Esta era originalmente Administrador/Chat/index.php
            require_once __DIR__ . '/../src/modules/chat/admin_chat_view.php'; 
            ?>
        </main>
    </div>
    <script src="assets/js/chat_admin.js"></script>
</body>
</html>