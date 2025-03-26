<?php
session_start();
include("conexion.php");

if (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];

    $sql = "SELECT modo_tema FROM usuario WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modo_tema = "claro"; // Valor por defecto
    if ($row = $result->fetch_assoc()) {
        $modo_tema = $row['modo_tema'];
    }

    $stmt->close();
    $conn->close();
}
?>  

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Sesión Docente</title>
    <link rel="stylesheet" href="Inicio/Docente/c-edua.css">
</head>

<body class="<?php echo ($modo_tema === 'oscuro') ? 'dark' : ''; ?>">
    <div class="login-container">
        <h1>Iniciar Sesión</h1>
        <div class="subtitle">DOCENTES</div>
        <form action="validar_login.php" method="POST">
            <div class="form-group">
                <label class="etiquetas">Usuario</label>
                <input type="text" name="usuario" placeholder="Usuario" required>
            </div>
            <div class="form-group">
                <label class="etiquetas">Contraseña</label>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="iniciar">INICIAR</button>
        </form>

    </div>
    <div class="admin-container">
        <h2 class="admin-title">¿ADMINISTRATIVO?</h2>
        <p class="admin-text">Inicia sesión en nuestro apartado de administrativos para poder ingresar a nuestra pagina
            web en modo administrativo.</p>
        <a href="Inicio/Administrador/index.php ">
            <button class="admin-button">Iniciar Sesión</button>
        </a>
    </div>
</body>

</html>