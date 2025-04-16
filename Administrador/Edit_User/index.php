<?php
include '../../conexion.php';
session_start();


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
$sql = "SELECT 
    u.id_usuario,
    u.email_usuario,
    u.nombre_usuario,
    u.telefono_usuario,
    u.id_institucion,
    m.id_materia,
    u.id_rol,
    r.tipo_rol AS nombre_rol,
    m.nombre_materia,
    i.nombre_institucion,
    u.fecha_nacimiento
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
<html lang="es">

<head>
    <?php include "../../SIDEBAR/Admin/head.php" ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="editcss.css">
    <title>Gestión de Usuarios</title>
</head>

<body>
    <?php include "../../SIDEBAR/Admin/sidebar.php" ?>

    <section class="home">
        <?php include '../../PHP/user_info.php'; ?>

        <div class="container">
            <div class="header">
                <h1>Gestión de Usuarios</h1>
            </div>

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
                <tbody>
                    <?php if ($resultado->num_rows > 0): ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr data-id-usuario="<?= $fila['id_usuario'] ?>"
                                data-telefono="<?= htmlspecialchars($fila['telefono_usuario']) ?>"
                                data-institucion="<?= $fila['id_institucion'] ?>" data-rol="<?= $fila['id_rol'] ?>"
                                data-materia="<?= $fila['id_materia'] ?>">

                                <td><img src="../../assets/avatar<?= ($fila['id_usuario'] % 4) + 1 ?>.jpg" alt="Avatar"></td>
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

            <div class="buttons">
                <button id="newUser">Crear nuevo usuario</button>
                <button id="save">Guardar</button>
            </div>
        </div>

        <!-- Modal Nuevo Usuario -->
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
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                    <div class="form-group">
                        <label for="telefono_usuario">Teléfono</label>
                        <input type="tel" id="telefono_usuario" name="telefono_usuario">
                    </div>
                    <div class="form-group">
                        <label for="id_materia">Materia</label>
                        <select id="id_materia" name="id_materia">
                            <option value="">Seleccionar materia</option>
                            <?php while ($materia = $materias->fetch_assoc()): ?>
                                <option value="<?= $materia['id_materia'] ?>">
                                    <?= htmlspecialchars($materia['nombre_materia']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_institucion">Institución</label>
                        <select id="id_institucion" name="id_institucion">
                            <?php while ($inst = $instituciones->fetch_assoc()): ?>
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

        <!-- Modal Editar Usuario -->
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
                        <label for="edit_fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="edit_fecha_nacimiento" name="fecha_nacimiento" required>
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
                            $materias->data_seek(0);
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
                            $instituciones->data_seek(0);
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
                    <!-- Agrega este botón antes del botón de submit -->
                    <div class="form-group">
                        <button type="button" id="openPasswordChange" class="password-change-btn">
                            Cambiar Contraseña
                        </button>
                    </div>
                    <button type="submit" class="submit-btn">Guardar Cambios</button>
                </form>
            </div>
        </div>
        <!-- Modal Cambiar Contraseña -->
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
        <script src="script.js"></script>
    </section>
</body>

</html>
<?php $conn->close(); ?>