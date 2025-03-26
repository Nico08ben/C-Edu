<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "../../SIDEBAR/Admin/head.php" ?>
    <link rel="stylesheet" href="editcss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Gestión de Usuarios</title>
</head>
<body>
    <?php include "../../SIDEBAR/Admin/sidebar.php" ?>

    <section class="home">
    <div class="container">
        <h1>Gestión de Usuarios</h1>
        <table>
            <thead>
                <tr>
                    <th>Foto Perfil</th>
                    <th>Nombre Completo</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Institución</th>
                    <th>Rol</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <tr>
                    <td><img src="perfil.png" alt="Perfil"></td>
                    <td>María Elena Rodríguez González</td>
                    <td>maria.rodriguez@comfandi.edu.co</td>
                    <td>123456789</td>
                    <td>Institución de prueba</td>
                    <td>Usuario</td>
                    <td class="action-buttons">
                        <button class="edit">✏️</button>
                        <button class="delete">🗑️</button>
                    </td>
                </tr>
                <tr>
                    <td><img src="perfil.png" alt="Perfil"></td>
                    <td>Marta Isabel Fernández López</td>
                    <td>arta.fernandez@comfandi.edu.co</td>
                    <td>987654321</td>
                    <td>Institución de prueba</td>
                    <td>Usuario</td>
                    <td class="action-buttons">
                        <button class="edit">✏️</button>
                        <button class="delete">🗑️</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button id="newUser">Crear nuevo usuario</button>
        <button id="save">Guardar</button>
    </div>

    <!-- Modal for New User -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <div class="modal-header">
                <h2>Crear Nuevo Usuario</h2>
            </div>
            <form id="newUserForm">
                <div class="form-group">
                    <label for="nombre_usuario">Nombre Completo</label>
                    <input type="text" id="nombre_usuario" name="nombre_usuario" required>
                </div>
                <div class="form-group">
                    <label for="email_usuario">Correo Electrónico</label>
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
                    <label for="id_institucion">Institución</label>
                    <select id="id_institucion" name="id_institucion">
                        <option value="1">Institución de prueba</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="id_rol">Rol</label>
                    <select id="id_rol" name="id_rol">
                        <option value="1">Usuario</option>
                    </select>
                </div>
                <button type="submit" class="submit-btn">Crear Usuario</button>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
    </section>
</body>
</html>