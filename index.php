
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="Inicio/styles.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body>
    <div class="container">
        <div class="container-form">
            <form class="sing-in" action="validar_login.php" method="POST">
                <h2>Iniciar Sesión</h2>
                <h4>Docentes</h4>
                
                <div class="social-networks">
                    <ion-icon name="logo-instagram"></ion-icon>
                </div>
                
                <span>Use su correo y contraseña</span>
                
                <div class="container-input">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="text" placeholder="Correo" name="email">
                </div>
                
                <div class="container-input">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" placeholder="Contraseña" name="password">
                </div>
                
                <button type="submit" class="boton" name="role" value="docente">Iniciar Sesión</button>
            </form>
        </div>

        <div class="container-form">
            <form class="sing-up" action="validar_login.php" method="POST">
                <h2>Iniciar Sesión</h2>
                <h4 class="administrativos">Administrativos</h4>
                
                <div class="social-networks">
                    <ion-icon name="logo-instagram"></ion-icon>
                </div>
                
                <span>Use su correo y contraseña</span>
                
                <div class="container-input">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="text" placeholder="Correo" name="email">
                </div>
                
                <div class="container-input">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" placeholder="Contraseña" name="password">
                </div>
                
                <button type="submit" class="boton" name="role" value="administrativo">Iniciar Sesión</button>
            </form>
        </div>

        <div class="container-welcome">
            <div class="welcome-sing-up welcome">
                <h3>¿ADMINISTRATIVO?</h3>
                <p>Inicia sesión en nuestro apartado de administrativos para ingresar a nuestra pagina web en modo administrativo</p>
                <button class="boton" id="btn-sign-up">Iniciar Sesión</button>
            </div>
            <div class="welcome-sing-in welcome">
                <h3>¿DOCENTE?</h3>
                <p>Inicia sesión en nuestro apartado de Docentes para ingresar a nuestra página web en modo Docentes.</p>
                <button class="boton" id="btn-sign-in">Iniciar Sesión</button>
            </div>
        </div>
    </div>
    <script src="Inicio/script.js"></script>
</body>
</html>