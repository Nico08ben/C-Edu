<!DOCTYPE html>
<html lang="es">
<head>
<?php include "../../SIDEBAR/head.php" ?>
    <link rel="stylesheet" href="profile.css">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="index.js" defer></script>
</head>
<body>
    <!-- Barra lateral -->
    <?php include "../../SIDEBAR/sidebar.php" ?>

    <section class="home">
        <!-- Contenido principal -->
    <div class="main-content">
        <!-- Tarjeta de perfil -->
        <div class="card-profile">
            <!-- Encabezado con opciones -->
            <div class="profile-header">
                <span class="profile-option" id="edit-profile">Editar Perfil</span>
                <span class="profile-option" id="help">Ayuda</span>
            </div>

            <div class="profile-container">
                <!-- Columna izquierda (Foto y Botón) -->
                <div class="profile-left">
                    <div class="user-avatar">
                        <img src="avatar.png">
                    </div>
                    <button class="edit-btn">EDITAR</button>
                </div>

                <!-- Columna derecha (Información) -->
                <div class="profile-right">
                    <div class="container">
                        <div class="columna">
                            <label>Nombre</label>
                            <input type="text" placeholder="Antonio Jose Rengifo Abonia" disabled>
                            
                            <label>Email</label>
                            <input type="text" placeholder="antoniojrequejo@comfandi.edu.co" disabled>
                            
                            <label>Fecha de Nacimiento</label>
                            <input type="text" placeholder="16 de Enero de 2001" disabled>
                            
                            <label>Materia</label>
                            <input type="text" placeholder="Matemáticas" disabled>
                            
                            <label>Colegio</label>
                            <input type="text" placeholder="Colegio Comfandi Calipso" disabled>
                        </div>

                        <div class="columna">
                            <label>Nombre de Usuario</label>
                            <input type="text" placeholder="antonio.reginfo.2" disabled>
                            
                            <label>Contraseña</label>
                            <input type="password" placeholder="************" disabled>
                            
                            <label>Teléfono</label>
                            <input type="text" placeholder="+57 315 6162888" disabled>
                            
                            <label>Grupo a Cargo</label>
                            <input type="text" placeholder="11-B" disabled>
                        </div>
                    </div>
                </div>  
            </div>
            <div class="ayuda">
                <h3>CONTACTANOS</h3>
                <p>Contacta con un asesor tecnico el cual te puede ayudar si presentas algun problema con nuestro programa. Puedes contactactarnos por medio de correo electronico, Whatsapp y numero de telefono.</p>
                <div class="correo">
                    <i class="fa-regular fa-envelope"></i>
                    <span>CORREO ELECTRONICO</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
                <div class="whatsapp">
                    <i class="fa-brands fa-whatsapp"></i>
                    <span>WHATSAPP</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
                <div class="correo">
                    <i class="fa-solid fa-phone"></i>
                    <span>TELEFONO</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </div>
        </div>
    </div>
    </section>
</body>
</html>
