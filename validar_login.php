<?php
session_start();
// Include the database connection file
include 'conexion.php';

// Check if the form was submitted using POST method and the required fields are set
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'], $_POST['role'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];
    $submitted_role = $_POST['role'];

    // MODIFICACIÓN: Seleccionar también nombre_usuario y foto_perfil_url
    $stmt = $conn->prepare("SELECT id_usuario, nombre_usuario, email_usuario, contrasena_usuario, id_rol, foto_perfil_url FROM usuario WHERE email_usuario = ?");

    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        if (password_verify($password, $fila['contrasena_usuario'])) {
            $database_role_id = $fila['id_rol'];
            $expected_role_id = null;
            if ($submitted_role == 'docente') {
                $expected_role_id = 1;
            } elseif ($submitted_role == 'administrativo') {
                $expected_role_id = 0; // Asumiendo que 0 es Admin, 1 es Docente, ajusta si es diferente
            }

            $role_match = false;
            if (($submitted_role == 'docente' && $database_role_id == 1) || ($submitted_role == 'administrativo' && $database_role_id == 0)) {
                $role_match = true;
            }
            // Si tienes más roles o una lógica diferente (ej. admin es CUALQUIER rol que NO SEA 1):
            // elseif ($submitted_role == 'administrativo' && $database_role_id != 1) {
            // $role_match = true;
            // }


            if ($role_match) {
                $_SESSION['id_usuario'] = $fila['id_usuario'];
                $_SESSION['nombre_usuario'] = $fila['nombre_usuario']; // Ahora 'nombre_usuario' está disponible
                $_SESSION['rol'] = $fila['id_rol']; // Usar el id_rol directamente
                $_SESSION['foto_perfil_url'] = $fila['foto_perfil_url']; // Guardar la URL de la foto en la sesión

                if ($fila['id_rol'] == 1) { // Docente
                    header("Location: Docente/Home/index.php");
                } elseif ($fila['id_rol'] == 0) { // Administrador
                    header("Location: Administrador/Home/index.php");
                } else {
                    // Manejar otros roles o un rol inesperado
                    header("Location: index.php?error=rol_desconocido");
                }
                exit();

            } else {
                $error_message = ($submitted_role == 'docente')
                    ? 'Tu cuenta no tiene permisos para iniciar sesión como Docente.'
                    : 'Tu cuenta no tiene permisos para iniciar sesión como Administrativo.';
                echo "<script>alert('" . htmlspecialchars($error_message) . "'); window.location.href='index.php';</script>";
            }
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Usuario no registrado'); window.location.href='index.php';</script>";
    }

    $stmt->close();
    $conn->close();

} else {
    header("Location: index.php");
    exit();
}
?>