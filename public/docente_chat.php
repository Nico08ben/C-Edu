<?php
session_start();

// Verificar si el usuario está logueado y es un docente (asumiendo que rol ID 1 es Docente)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) {
    // Si no está logueado o no es docente, redirigir a la página de login
    header("Location: index.php");
    exit;
}

$page_title = "Chat - Docente"; // Título para esta página

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php
    // Incluimos la cabecera específica del docente
    require_once __DIR__ . '/../src/includes/docente_head.php';
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="assets/css/docente_sidebar.css">
    <link rel="stylesheet" href="assets/css/chat_docente.css">
</head>

<body>
    <?php
    // Incluimos la barra lateral específica del docente
    require_once __DIR__ . '/../src/includes/docente_sidebar.php';
    ?>

    <?php
    // Incluimos la vista principal del chat del docente
    // Esta era originalmente Docente/Chat/index.php
    require_once __DIR__ . '/../src/modules/chat/docente_chat_view.php';
    ?>

    <script src="assets/js/chat_docente.js"></script>
</body>

</html>