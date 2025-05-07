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
$page_title = '';
$css_sidebar_file = '';   // Renombrado para mayor claridad (antes $csssidebar)
$css_profile_file = '';   // Nueva variable para el CSS del perfil

$base_css_path = 'assets/css/'; // Asumiendo que este script está en una carpeta como 'user_profile/'
// y 'assets' está un nivel arriba.
// Si este script está en la raíz con 'assets', usa 'assets/css/'

if ($rol_usuario_actual == 1) { // Docente
    $head_file = __DIR__ . '/../src/includes/docente_head.php';
    $sidebar_file = __DIR__ . '/../src/includes/docente_sidebar.php';
    $profile_view_file = __DIR__ . '/../src/modules/user_profile/docente_profile_view.php';
    $page_title = "Perfil del Docente";
    $css_sidebar_file = $base_css_path . 'docente_sidebar.css';
    $css_profile_file = $base_css_path . 'docente_user_profile.css'; // CSS específico del perfil del docente
} else { // Administrador (asumiendo cualquier rol que no sea 1 es admin, ajusta si tienes más roles)
    $head_file = __DIR__ . '/../src/includes/admin_head.php';
    $sidebar_file = __DIR__ . '/../src/includes/admin_sidebar.php';
    $profile_view_file = __DIR__ . '/../src/modules/user_profile/admin_profile_view.php';
    $page_title = "Perfil del Administrador";
    $css_sidebar_file = $base_css_path . 'admin_sidebar.css';
    $css_profile_file = $base_css_path . 'admin_user_profile.css'; // CSS específico del perfil del admin
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
        // error_log("Archivo head no encontrado: " . $head_file);
    }
    ?>
    <title><?= htmlspecialchars($page_title) ?></title>

    <?php // Cargar el CSS del Sidebar ?>
    <?php if (!empty($css_sidebar_file)): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($css_sidebar_file); ?>">
    <?php endif; ?>

    <?php // Cargar el CSS específico del Perfil de Usuario ?>
    <?php if (!empty($css_profile_file)): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars($css_profile_file); ?>">
    <?php endif; ?>

</head>

<body>
    <?php
    // Incluimos la barra lateral específica del rol
    if (file_exists($sidebar_file)) {
        require_once $sidebar_file;
    } else {
        echo "<p>Error: No se encontró el archivo sidebar (" . htmlspecialchars($sidebar_file) . ").</p>";
        // error_log("Archivo sidebar no encontrado: " . $sidebar_file);
    }
    ?>

    <?php
    // Incluimos la vista de perfil específica del rol
    if (file_exists($profile_view_file)) {
        require_once $profile_view_file;
    } else {
        echo "<p>Error: No se encontró la vista de perfil (" . htmlspecialchars($profile_view_file) . ").</p>";
        // error_log("Archivo profile_view no encontrado: " . $profile_view_file);
    }
    ?>

    <script src="assets/js/user_profile.js"></script>

</body>
</html>