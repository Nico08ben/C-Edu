<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir conexion.php SOLO si no está ya incluido por la página principal
// Es común que la página que incluye user_info.php ya haya establecido la conexión.
if (!isset($conn) || !$conn) {
    require_once __DIR__ . "/../conexion.php";
}

$id_usuario = $_SESSION['id_usuario'] ?? null;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Invitado';
$foto_perfil_url_de_sesion = $_SESSION['foto_perfil_url'] ?? null;
$rol_id_de_sesion = $_SESSION['rol'] ?? null; // Asumiendo que 'rol' en sesión guarda el ID del rol

$rol_display = 'Sin rol';

if ($id_usuario && $rol_id_de_sesion !== null) {
    // Obtener el nombre del rol y la materia (si aplica) desde la BD
    // Esto es útil si solo tienes el ID del rol en sesión y necesitas el nombre.
    $stmt_details = $conn->prepare(
        "SELECT r.tipo_rol, m.nombre_materia 
         FROM usuario u
         JOIN rol r ON u.id_rol = r.id_rol
         LEFT JOIN materia m ON u.id_materia = m.id_materia
         WHERE u.id_usuario = ?"
    );
    if ($stmt_details) {
        $stmt_details->bind_param("i", $id_usuario);
        $stmt_details->execute();
        $result_details = $stmt_details->get_result()->fetch_assoc();
        if ($result_details) {
            $rol_display = htmlspecialchars($result_details['tipo_rol']);
            // Si el rol es 'Maestro' (o el texto que uses) y tiene una materia asignada
            if (isset($result_details['tipo_rol']) && strtolower($result_details['tipo_rol']) === 'maestro' && !empty($result_details['nombre_materia'])) {
                $rol_display = "Maestro de " . htmlspecialchars($result_details['nombre_materia']);
            }
        }
        $stmt_details->close();
    } else {
        // Si falla la consulta, podrías usar el ID del rol para mostrar algo genérico
        $rol_display = ($rol_id_de_sesion == 0) ? "Administrador" : (($rol_id_de_sesion == 1) ? "Maestro" : "Rol desconocido");
    }
}

// Construir la ruta a la imagen
// Asumimos que user_info.php está en /C-Edu/PHP/
// y las imágenes de perfil están en /C-Edu/uploads/profile_pictures/
// y la imagen por defecto en /C-Edu/assets/
// Imagen por defecto

$profile_image_url = '/C-edu/uploads/profile_pictures/default-avatar.png'; // Imagen por defecto
if (!empty($user_profile_data['foto_perfil_url'])) {
    // Construir la ruta completa a la imagen.
    // Asumimos que foto_perfil_url guarda algo como "uploads/profile_pictures/image.jpg"
    // y que index.php está dos niveles arriba de esa carpeta.
    $profile_image_url = '../../' . htmlspecialchars($user_profile_data['foto_perfil_url']);
} else if (isset($_SESSION['foto_perfil_url']) && !empty($_SESSION['foto_perfil_url'])) {
    // Fallback a la imagen de sesión si existe (podría ser útil si la BD tarda en actualizarse para la vista)
    $profile_image_url = '../../' . htmlspecialchars($_SESSION['foto_perfil_url']);
}

?>

<div id="user-profile-box">

    <?php include __DIR__ . "/notificacion.php"; // Ruta corregida para incluir notificacion.php ?>

    <div class="profile-text">
        <span class="name"><?= htmlspecialchars($nombre_usuario) ?></span>
        <span class="role"><?= $rol_display ?></span>
    </div>

    <a href="/C-Edu/<?php echo ($_SESSION['rol'] == 0 ? 'Administrador' : 'Docente'); ?>/UserProfile/index.php"
        class="profile-image-link">
        <img src="<?= $profile_image_url ?>?t=<?= time() ?>" alt="Foto de perfil"class="profile-image">
    </a>
</div>
</div>