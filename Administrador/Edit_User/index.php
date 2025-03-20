<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "../../SIDEBAR/Admin/head.php" ?>
    <link rel="stylesheet" href="editcss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Editar Usuario</title>
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
                    <th>Materia</th>
                    <th>Correo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="userTable">
                <tr>
                    <td><img src="avatar1.png" alt="Perfil"></td>
                    <td>María Elena Rodríguez González</td>
                    <td>Química</td>
                    <td>maria.rodriguez@comfandi.edu.co</td>
                    <td>
                        <button class="edit">✏️</button>
                        <button class="delete">🗑️</button>
                    </td>
                </tr>
                <tr>
                    <td><img src="avatar2.png" alt="Perfil"></td>
                    <td>Marta Isabel Fernández López</td>
                    <td>Física</td>
                    <td>arta.fernandez@comfandi.edu.co</td>
                    <td>
                        <button class="edit">✏️</button>
                        <button class="delete">🗑️</button>
                    </td>
                </tr>
            </tbody>
        </table>

        <button id="newUser">Crear nuevo usuario</button>
        <button id="save">Guardar</button>
    </div>

    <script src="script.js"></script>
    </section>
        
</body>
</html>