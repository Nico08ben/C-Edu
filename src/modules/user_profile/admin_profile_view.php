<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../config/database.php');

$id_usuario = $_SESSION['id_usuario'] ?? null;
$user_profile_data = []; // Array para almacenar todos los datos del perfil

if ($id_usuario) {
    $stmt = $conn->prepare("SELECT u.nombre_usuario, u.email_usuario, u.telefono_usuario, u.grupo_cargo_usuario,
                                u.foto_perfil_url, 
                                r.tipo_rol AS nombre_rol, 
                                m.nombre_materia AS nombre_materia, 
                                i.nombre_institucion
                                FROM usuario u
                                INNER JOIN rol r ON u.id_rol = r.id_rol 
                                LEFT JOIN materia m ON u.id_materia = m.id_materia 
                                LEFT JOIN institucion i ON u.id_institucion = i.id_institucion
                                WHERE u.id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_profile_data = $result->fetch_assoc();
    $stmt->close();
}

// Determinar la URL de la imagen de perfil
$profile_image_url = '/assets/images/avatar_default.png';
if (!empty($user_profile_data['foto_perfil_url'])) {
    $profile_image_url = '/' . htmlspecialchars($user_profile_data['foto_perfil_url']);
} else if (isset($_SESSION['foto_perfil_url']) && !empty($_SESSION['foto_perfil_url'])) {
    $profile_image_url = '/' . htmlspecialchars($_SESSION['foto_perfil_url']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php 
    if (strpos(__DIR__, 'Administrador') !== false) {
        include "../../SIDEBAR/Admin/head.php";
    } elseif (strpos(__DIR__, 'Docente') !== false) {
        include "../../SIDEBAR/Docente/head.php";
    }
    ?>
    <link rel="stylesheet" href="profile.css">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="index.js" defer></script>
</head>
<body>
    <?php 
    if (strpos(__DIR__, 'Administrador') !== false) {
        include "../../SIDEBAR/Admin/sidebar.php";
    } elseif (strpos(__DIR__, 'Docente') !== false) {
        include "../../SIDEBAR/Docente/sidebar.php";
    }
    ?>

    <section class="home">
        <div class="main-content">
            <div class="card-profile">
                <div class="profile-header">
                    <span class="profile-option" id="edit-profile" style="font-weight: bold; cursor: pointer;">Editar Perfil</span>
                    <span class="profile-option" id="help">Ayuda</span>
                </div>

                <div class="profile-container">
                    <div class="profile-left">
                        <div class="user-avatar">
                            <img src="<?= $profile_image_url ?>?t=<?= time() ?>" alt="Foto de perfil">
                        </div>
                        <form id="upload-form" enctype="multipart/form-data">
                            <input type="file" id="profile-image-input" name="profile_image" accept="image/*" style="display: none;">
                            <button type="button" class="edit-btn">EDITAR</button>
                            <div id="upload-status"></div>
                        </form>
                    </div>

                    <div class="profile-right">
                        <div class="container">
                            <div class="columna">
                                <label>Nombre</label>
                                <input type="text" name="nombre_completo" value="<?= htmlspecialchars($user_profile_data['nombre_usuario'] ?? '') ?>" readonly>

                                <label>Email</label>
                                <input type="text" name="correo" value="<?= htmlspecialchars($user_profile_data['email_usuario'] ?? '') ?>" readonly>

                                <label>Materia</label>
                                <input type="text" name="materia" value="<?= htmlspecialchars($user_profile_data['nombre_materia'] ?? 'No asignada') ?>" readonly>

                                <label>Colegio</label>
                                <input type="text" name="colegio" value="<?= htmlspecialchars($user_profile_data['nombre_institucion'] ?? 'No asignada') ?>" readonly>
                            </div>

                            <div class="columna">
                                <label>Rol</label> <input type="text" name="rol_usuario" value="<?= htmlspecialchars($user_profile_data['nombre_rol'] ?? '') ?>" readonly>

                                <label>Contraseña</label>
                                <input type="password" name="password" value="************" readonly>

                                <label>Teléfono</label>
                                <input type="text" name="telefono" value="<?= htmlspecialchars($user_profile_data['telefono_usuario'] ?? '') ?>" readonly>

                                <label>Grupo a Cargo</label>
                                <input type="text" name="grupo_cargo" value="<?= htmlspecialchars($user_profile_data['grupo_cargo_usuario'] ?? 'No asignado') ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ayuda" style="display: none;">
                    <h3>CONTACTANOS</h3>
                    <p>Contacta con un asesor técnico si presentas algún problema. Puedes escribirnos por correo electrónico, WhatsApp o teléfono.</p>
                    <div class="correo"><i class="fa-regular fa-envelope"></i><span>CORREO ELECTRÓNICO</span><i class="fa-solid fa-chevron-right"></i></div>
                    <div class="whatsapp"><i class="fa-brands fa-whatsapp"></i><span>WHATSAPP</span><i class="fa-solid fa-chevron-right"></i></div>
                    <div class="telefono-ayuda"><i class="fa-solid fa-phone"></i><span>TELÉFONO</span><i class="fa-solid fa-chevron-right"></i></div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>