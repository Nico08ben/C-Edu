<?php
session_start(); // Iniciar sesión

include 'conexion.php'; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Consulta para verificar si el usuario existe
    $sql = "SELECT * FROM usuario WHERE email_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        // Verificar la contraseña usando password_verify()
        if (password_verify($password, $fila['contraseña_usuario'])) {
            // Inicio de sesión exitoso
            $_SESSION['id_usuario'] = $fila['id_usuario'];
            $_SESSION['nombre_usuario'] = $fila['nombre_usuario'];
            $_SESSION['rol'] = $fila['id_rol']; // Guardamos el rol para diferenciar acceso

            // Redirigir al área correspondiente según el rol
            if ($fila['id_rol'] == 1) { 
                header("Location: Docente/Home/index.php");
            } else {
                echo "<script>alert('Usuario corresponde a administrador'); window.location.href='index.php';</script>";
            }
            exit();
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='index.php';</script>";
        }
    } else {
        echo "<script>alert('Usuario no registrado'); window.location.href='index.php';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
