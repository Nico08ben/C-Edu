<?php
include '../../conexion.php';
session_start();
$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}

// ... resto del código


// Verificar sesión y rol de administrador (0 = Admin)
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 0) {
    header("Location: ../../Inicio/Administrador/index.php");
    exit;
}

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];


// Consulta ajustada para nueva estructura de roles
// Consulta ajustada para nueva estructura de roles
$sql = "SELECT
    u.id_usuario,
    u.email_usuario,
    u.nombre_usuario,
    u.telefono_usuario,
    u.id_institucion,
    u.foto_perfil_url,
    m.id_materia,
    u.id_rol,
    r.tipo_rol AS nombre_rol,
    m.nombre_materia,
    i.nombre_institucion
FROM usuario u
LEFT JOIN materia m ON u.id_materia = m.id_materia
LEFT JOIN institucion i ON u.id_institucion = i.id_institucion
LEFT JOIN rol r ON u.id_rol = r.id_rol";
$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}

// Obtener instituciones y materias
$instituciones = $conn->query("SELECT id_institucion, nombre_institucion FROM institucion");
$materias = $conn->query("SELECT id_materia, nombre_materia FROM materia");
?>

<!DOCTYPE html>
<html lang="es" class="<?php echo $theme_class;?>">

<head>
    <?php include "../../SIDEBAR/Admin/head.php" ?> 
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="editcss.css">
    <title>Gestión de Usuarios</title>
</head>

<body>
    <?php include "../../SIDEBAR/Admin/sidebar.php" ?>

    <section class="home">
        <div class="user-profile-display"><?php include '../../PHP/user_info.php'; ?></div>
        

        <div class="container">
            <div class="header">
                <h1>Gestión de Usuarios</h1>
            </div>

            <div class="table-scroll-container">
    <table>
        <thead>
            <tr>
                <th>Foto Perfil</th>
                <th>Nombre Completo</th>
                <th>Materia</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Acciones</th>
            </tr>
        </thead>
    </table>
    <div class="tbody-wrapper">
        <table>
            <tbody>
            <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr data-id-usuario="<?= $fila['id_usuario'] ?>"
                                data-telefono="<?= htmlspecialchars($fila['telefono_usuario']) ?>"
                                data-institucion="<?= $fila['id_institucion'] ?>" data-rol="<?= $fila['id_rol'] ?>"
                                data-materia="<?= $fila['id_materia'] ?>">

                                <?php
// Define la ruta del avatar por defecto
$defaultAvatar = '../../uploads/profile_pictures/default-avatar.png';
$avatarPath = $defaultAvatar; // Usar el avatar por defecto inicialmente

// Verifica si hay una URL de foto de perfil y no está vacía
if (!empty($fila['foto_perfil_url'])) {
    // Construye la ruta a la foto de perfil del usuario.
    // Se asume que 'foto_perfil_url' guarda una ruta como 'uploads/profile_pictures/nombre_archivo.png'
    // y que tu archivo index.php está dos niveles por debajo de la raíz del proyecto
    // (ej. RaizProyecto/Admin/Usuarios/index.php)
    // y la carpeta 'uploads' está en la raíz del proyecto (ej. RaizProyecto/uploads/).
    $userProfilePic = '../../' . $fila['foto_perfil_url'];

    // Opcional: Podrías verificar si el archivo existe en el servidor aquí,
    // pero para simplificar, asumiremos que si la URL está, la imagen existe.
    // Ejemplo de verificación (requiere ajustar la ruta base según tu estructura):
    // if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/ruta_base_proyecto/' . $fila['foto_perfil_url'])) {
    //     $avatarPath = $userProfilePic;
    // } else {
    //     // Si el archivo no existe, puedes registrar un log o mantener el avatar por defecto.
    // }
    $avatarPath = $userProfilePic; // Si no haces la verificación de existencia, usa directamente la foto del usuario.
}
?>
<td><img src="<?= htmlspecialchars($avatarPath) ?>" alt="Avatar" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;"></td>
                                <td><?= htmlspecialchars($fila['nombre_usuario']) ?></td>
                                <td><?= htmlspecialchars($fila['nombre_materia'] ?? 'Sin asignar') ?></td>
                                <td><?= htmlspecialchars($fila['email_usuario']) ?></td>
                                <td><?= htmlspecialchars($fila['nombre_rol'] ?? 'Sin asignar') ?></td>
                                <td class="action-buttons">
                                    <button class="edit" data-id="<?= $fila['id_usuario'] ?>"><i
                                            class="fas fa-edit"></i></button>
                                    <button class="delete" data-id="<?= $fila['id_usuario'] ?>"><i
                                            class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No hay usuarios registrados</td>
                        </tr>
                    <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

            <div class="buttons">
                <button id="newUser">Crear nuevo usuario</button>
            </div>
        </div>

        <div id="userModal" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2 class="modal-title">Crear Nuevo Usuario</h2>
                <form id="newUserForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <div class="form-group">
                        <label for="nombre_usuario">Nombre Completo</label>
                        <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="email_usuario">Correo</label>
                        <input type="email" id="email_usuario" name="email_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="contraseña_usuario">Contraseña</label>
                        <input type="password" id="contraseña_usuario" name="contraseña_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono_usuario">Teléfono</label>
                        <input type="tel" id="telefono_usuario" name="telefono_usuario">
                    </div>
                    <div class="form-group">
                        <label for="id_materia">Materia</label>
                        <select id="id_materia" name="id_materia">
                            <option value="">Seleccionar materia</option>
                            <?php 
                            $materias->data_seek(0); // Reiniciar puntero para el bucle
                            while ($materia = $materias->fetch_assoc()): ?>
                                <option value="<?= $materia['id_materia'] ?>">
                                    <?= htmlspecialchars($materia['nombre_materia']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_institucion">Institución</label>
                        <select id="id_institucion" name="id_institucion">
                            <?php 
                            $instituciones->data_seek(0); // Reiniciar puntero
                            while ($inst = $instituciones->fetch_assoc()): ?>
                                <option value="<?= $inst['id_institucion'] ?>">
                                    <?= htmlspecialchars($inst['nombre_institucion']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_rol">Rol</label>
                        <select id="id_rol" name="id_rol">
                            <option value="0">Administrador</option>
                            <option value="1">Maestro</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Crear Usuario</button>
                </form>
            </div>
        </div>

        <div id="editUserModal" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <h2 class="modal-title">Editar Usuario</h2>
                <form id="editUserForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" id="edit_id_usuario" name="id_usuario">
                    <div class="form-group">
                        <label for="edit_nombre_usuario">Nombre</label>
                        <input type="text" id="edit_nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email_usuario">Correo</label>
                        <input type="email" id="edit_email_usuario" name="email_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_telefono_usuario">Teléfono</label>
                        <input type="tel" id="edit_telefono_usuario" name="telefono_usuario">
                    </div>
                    <div class="form-group">
                        <label for="edit_id_materia">Materia</label>
                        <select id="edit_id_materia" name="id_materia">
                            <option value="">Sin materia</option>
                            <?php
                            $materias->data_seek(0); // Reiniciar puntero
                            while ($materia = $materias->fetch_assoc()): ?>
                                <option value="<?= $materia['id_materia'] ?>">
                                    <?= htmlspecialchars($materia['nombre_materia']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_institucion">Institución</label>
                        <select id="edit_id_institucion" name="id_institucion">
                            <?php
                            $instituciones->data_seek(0); // Reiniciar puntero
                            while ($inst = $instituciones->fetch_assoc()): ?>
                                <option value="<?= $inst['id_institucion'] ?>">
                                    <?= htmlspecialchars($inst['nombre_institucion']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_rol">Rol</label>
                        <select id="edit_id_rol" name="id_rol">
                            <option value="0">Administrador</option>
                            <option value="1">Maestro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <button type="button" id="openPasswordChange" class="password-change-btn">
                            Cambiar Contraseña
                        </button>
                    </div>
                    <button type="submit" class="submit-btn">Guardar Cambios</button>
                </form>
            </div>
        </div>
        <div id="changePasswordModal" class="modal">
            <div class="modal-content">
                <span class="close-password">&times;</span>
                <h2 class="modal-title">Cambiar Contraseña</h2>
                <form id="changePasswordForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    <input type="hidden" id="password_user_id" name="id_usuario">

                    <div class="form-group">
                        <label for="new_password">Nueva Contraseña</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="submit-btn">Cambiar Contraseña</button>
                </form>
            </div>
        </div>
        <script src="script.js"></script> </section>
</body>

</html>
<?php $conn->close(); ?>