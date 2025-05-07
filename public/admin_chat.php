<?php
session_start();

// Verificar si el usuario está logueado y es un administrador 
// Ajusta la condición de $_SESSION['rol'] si tienes un ID específico para admin, ej. $_SESSION['rol'] == 2
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 1) { // Asumiendo que rol 1 es admin, cualquier otro es Admin
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
    // Incluimos la cabecera específica del admin
    require_once __DIR__ . '/../src/includes/admin_head.php';
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="assets/css/admin_sidebar.css">
    <link rel="stylesheet" href="assets/css/chat_admin.css">
</head>

<body>
    <?php
    // Incluimos la barra lateral específica del admin
    require_once __DIR__ . '/../src/includes/admin_sidebar.php';
    ?>

    <?php
    // Incluimos la vista principal del chat del admin
    // Esta era originalmente admin/Chat/index.php
    require_once __DIR__ . '/../src/modules/chat/admin_chat_view.php';
    ?>

    <script src="assets/js/chat_admin.js"></script>
</body>

</html>