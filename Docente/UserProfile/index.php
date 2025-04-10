<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../../conexion.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;

if ($id_usuario) {
    $stmt = $conn->prepare("SELECT u.nombre_usuario, u.email_usuario, u.telefono_usuario, u.grupo_cargo_usuario,
                                r.tipo_rol AS nombre_rol, m.nombre_materia AS nombre_materia, i.nombre_institucion
                                FROM usuario u
                                INNER JOIN rol r ON u.id_rol = r.id_rol 
                                LEFT JOIN materia m ON u.id_materia = m.id_materia 
                                LEFT JOIN institucion i ON u.id_institucion = i.id_institucion
                                WHERE u.id_usuario = ?
                                            ");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $fila = $result->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "../../SIDEBAR/Docente/head.php" ?>
    <link rel="stylesheet" href="profile.css">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="index.js" defer></script>
</head>

<body>
    <?php include "../../SIDEBAR/Docente/sidebar.php" ?>

    <section class="home">
        <div class="main-content">
            <div class="card-profile">
                <div class="profile-header">
                    <span class="profile-option" id="edit-profile" style="font-weight: bold; cursor: pointer;">Editar
                        Perfil</span>
                    <span class="profile-option" id="help">Ayuda</span>
                </div>

                <div class="profile-container">
                    <div class="profile-left">
                        <div class="user-avatar">
                        </div>
                        <form id="upload-form" enctype="multipart/form-data">
                            <input type="file" id="profile-image-input" name="profile_image" accept="image/*"
                                style="display: none;">
                            <button type="button" class="edit-btn">EDITAR</button>
                            <div id="upload-status"></div>
                        </form>
                    </div>

                    <div class="profile-right">
                        <div class="container">
                            <div class="columna">
                                <label>Nombre</label>
                                <input type="text" name="nombre_completo"
                                    value="<?= htmlspecialchars($fila['nombre_usuario'] ?? '') ?>" readonly>

                                <label>Email</label>
                                <input type="text" name="correo"
                                    value="<?= htmlspecialchars($fila['email_usuario'] ?? '') ?>" readonly>

                                <label>Fecha de Nacimiento</label>
                                <input type="text" name="fecha_nacimiento"
                                    value="<?= htmlspecialchars($fila['fecha_nacimiento'] ?? '') ?>" readonly>

                                <label>Materia</label>
                                <input type="text" name="materia"
                                    value="<?= htmlspecialchars($fila['nombre_materia'] ?? '') ?>" readonly>

                                <label>Colegio</label>
                                <input type="text" name="colegio"
                                    value="<?= htmlspecialchars($fila['nombre_institucion'] ?? '') ?>" readonly>
                            </div>

                            <div class="columna">
                                <label>Nombre de Usuario</label>
                                <input type="text" name="usuario"
                                    value="<?= htmlspecialchars($fila['nombre_usuario'] ?? '') ?>" readonly>

                                <label>Contraseña</label>
                                <input type="password" name="password" value="************" readonly>

                                <label>Teléfono</label>
                                <input type="text" name="telefono"
                                    value="<?= htmlspecialchars($fila['telefono_usuario'] ?? '') ?>" readonly>

                                <label>Grupo a Cargo</label>
                                <input type="text" name="grupo_cargo"
                                    value="<?= htmlspecialchars($fila['grupo_cargo_usuario'] ?? '') ?>" readonly>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="ayuda">
                    <h3>CONTACTANOS</h3>
                    <p>Contacta con un asesor técnico si presentas algún problema. Puedes escribirnos por correo
                        electrónico, WhatsApp o teléfono.</p>
                    <div class="correo">
                        <i class="fa-regular fa-envelope"></i>
                        <span>CORREO ELECTRÓNICO</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                    <div class="whatsapp">
                        <i class="fa-brands fa-whatsapp"></i>
                        <span>WHATSAPP</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                    <div class="correo">
                        <i class="fa-solid fa-phone"></i>
                        <span>TELÉFONO</span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>

</html>