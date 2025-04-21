<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./index.css">
    <title>Inicio Sesión Administrador</title>
</head>

<body>
    <main>
        <div class="container">
            <div class="Login-container">
                <h1 id="Titulo-Login">Iniciar Sesión</h1>
                <p id="Subtitulo-login">ADMINISTRADORES</p>
                <form class="Login" action="../../validar_login.php" method="POST">
                    <label for="campo-usuario" id="p-usuario">Usuario</label>
                    <input id="campo-usuario" type="text" name="usuario" placeholder="Usuario" required>
                    <label for="campo-contraseña" id="p-contraseña">Contraseña</label>
                    <input id="campo-contraseña" type="password" name="password" placeholder="Contraseña" required>
                    <button id="button-login" type="submit">INICIAR</button>
                </form>
            </div>
            <div class="Docente-container">
                <h2 id="titulo-docente">¿DOCENTE?</h2>
                <p id="p-docente">Inicia sesión en nuestro apartado de Docentes para poder ingresar a nuestra página web
                    en modo Docentes.</p>
                <a href="../../index.php">
                    <button id="button-docente">Iniciar Sesion</button>
                </a>
            </div>
        </div>
    </main>
</body>

</html>