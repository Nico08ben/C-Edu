<!DOCTYPE html>
<html lang="es">
<head>
<?php include "../../SIDEBAR/Admin/head.php" ?>
    <link rel="stylesheet" href="profile.css">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="index.js" defer></script>
</head>
<body>
    <!-- Barra lateral -->
    <?php include "../../SIDEBAR/Admin/sidebar.php" ?>

    <section class="home">
        <!-- Contenido principal -->
    <div class="main-content">
        <!-- Tarjeta de perfil -->
        <div class="card-profile">
            <!-- Encabezado con opciones -->
            <div class="profile-header">
                <span class="profile-option" id="edit-profile" style="font-weight: bold;">Editar Perfil</span>
                <span class="profile-option" id="help">Ayuda</span>
            </div>

            <div class="profile-container">
                <!-- Columna izquierda (Foto y Botón) -->
                <div class="profile-left">
                    <div class="user-avatar">
                        <?php
                        // Aquí obtendrías la imagen de la base de datos
                        // Por ejemplo: $userId = $_SESSION['id_usuario']; 
                        $userId = 6; // Usamos un ID de prueba, ajustar según corresponda
                        
                        // Incluir archivo de conexión a la base de datos
                        // Corregir la ruta del archivo de conexión
                        $connection_file = "../../C-EDU/conexion.php";
                        if (file_exists($connection_file)) {
                            include $connection_file;
                        } else {
                            // Fallback a la ruta alternativa
                            include "../../config/conexion.php";
                        }
                        
                        // Verificar si la conexión existe
                        if (isset($conn)) {
                            // Consultar la imagen del usuario
                            $query = "SELECT foto_perfil, foto_tipo FROM usuario WHERE id_usuario = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                if ($row['foto_perfil']) {
                                    // Si hay una imagen en la base de datos, mostrarla
                                    $imgData = base64_encode($row['foto_perfil']);
                                    $imgType = $row['foto_tipo'];
                                    echo "<img src='data:$imgType;base64,$imgData'>";
                                } else {
                                    // Si no hay imagen, mostrar la predeterminada
                                    echo "<img src='avatar.png'>";
                                }
                            } else {
                                // Si no se encuentra el usuario, mostrar imagen predeterminada
                                echo "<img src='avatar.png'>";
                            }
                            $stmt->close();
                        } else {
                            // Si no hay conexión, mostrar imagen predeterminada
                            echo "<img src='avatar.png'>";
                        }
                        ?>
                    </div>
                    <form id="upload-form" enctype="multipart/form-data">
                        <input type="file" id="profile-image-input" name="profile_image" accept="image/*" style="display: none;">
                        <button type="button" class="edit-btn">EDITAR</button>
                        <div id="upload-status"></div>
                    </form>
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