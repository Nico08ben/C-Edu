<?php
include '../../conexion.php'; // Conexión a la base de datos

// Consulta modificada que no hace referencia a la tabla materia
$sql = "SELECT id_usuario, email_usuario, nombre_usuario, telefono_usuario, 
        id_institucion, id_rol 
        FROM usuario";
$resultado = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "../../SIDEBAR/Admin/head.php" ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="editcss.css">
    <title>Gestión de Usuarios</title>
</head>

<body>
    <?php include "../../SIDEBAR/Admin/sidebar.php" ?>

    <section class="home">
        <div class="user-info">
            <div class="notifications">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div class="profile">
                <img src="../../assets/perfil.jpg" alt="Perfil">
                <div class="profile-text">
                    <span class="name">Fernando</span>
                    <span class="role">Coordinador</span>
                </div>
            </div>
        </div>
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
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Definición de materias para asignar según el ID del usuario
                    $materias = [
                        "Matemáticas",
                        "Física",
                        "Química",
                        "Lenguaje"
                    ];

                    if ($resultado->num_rows > 0) {
                        while ($fila = $resultado->fetch_assoc()) {
                            // Asignar materia basado en el ID del usuario para simular la relación
                            $materiaNombre = $materias[$fila["id_usuario"] % count($materias)];

                            echo "<tr>";
                            echo "<td><img src='../../assets/avatar" . (($fila["id_usuario"] % 4) + 1) . ".jpg' alt='Avatar'></td>";
                            echo "<td>" . $fila["nombre_usuario"] . "</td>";
                            echo "<td>" . $materiaNombre . "</td>";
                            echo "<td>" . $fila["email_usuario"] . "</td>";
                            echo "<td class='action-buttons'>
                                    <button class='edit'><i class='fas fa-edit'></i></button>
                                    <button class='delete'><i class='fas fa-trash'></i></button>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No hay usuarios registrados</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>

            <div class="buttons">
                <button id="newUser">Crear nuevo usuario</button>
                <button id="save">Guardar</button>
            </div>
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
                        <label for="materia">Materia</label>
                        <select id="materia" name="materia">
                            <option value="Matemáticas">Matemáticas</option>
                            <option value="Física">Física</option>
                            <option value="Química">Química</option>
                            <option value="Lenguaje">Lenguaje</option>
                        </select>
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
                            <option value="1">Docente</option>
                            <option value="2">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Crear Usuario</button>
                </form>
            </div>
        </div>

        <!-- Modal for Edit User -->
        <div id="editUserModal" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <div class="modal-header">
                    <h2>Editar Usuario</h2>
                </div>
                <form id="editUserForm">
                    <input type="hidden" id="edit_id_usuario" name="id_usuario">
                    <div class="form-group">
                        <label for="edit_nombre_usuario">Nombre Completo</label>
                        <input type="text" id="edit_nombre_usuario" name="nombre_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email_usuario">Correo Electrónico</label>
                        <input type="email" id="edit_email_usuario" name="email_usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_telefono_usuario">Teléfono</label>
                        <input type="tel" id="edit_telefono_usuario" name="telefono_usuario">
                    </div>
                    <div class="form-group">
                        <label for="edit_materia">Materia</label>
                        <select id="edit_materia" name="materia">
                            <option value="Matemáticas">Matemáticas</option>
                            <option value="Física">Física</option>
                            <option value="Química">Química</option>
                            <option value="Lenguaje">Lenguaje</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_institucion">Institución</label>
                        <select id="edit_id_institucion" name="id_institucion">
                            <option value="1">Institución de prueba</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit_id_rol">Rol</label>
                        <select id="edit_id_rol" name="id_rol">
                            <option value="1">Docente</option>
                            <option value="2">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="submit-btn">Guardar Cambios</button>
                </form>
            </div>
        </div>

        <script src="script.js"></script>
    </section>
</body>

</html>