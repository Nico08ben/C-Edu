<!DOCTYPE html>
<html lang="es">
<head>
<?php 
// Intentar incluir el archivo head.php
$head_file = "../../SIDEBAR/Admin/head.php";
if (file_exists($head_file)) {
    include $head_file;
} 
?>
    <link rel="stylesheet" href="profile.css">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <script src="index.js" defer></script>
</head>
<body>
    <!-- Barra lateral -->
    <?php 
    // Intentar incluir el archivo sidebar.php
    $sidebar_file = "../../SIDEBAR/Admin/sidebar.php";
    if (file_exists($sidebar_file)) {
        include $sidebar_file;
    }
    ?>

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
                        session_start();
                        
                        // Aquí obtendrías la imagen de la base de datos
                        // Por ejemplo: $userId = $_SESSION['id_usuario']; 
                        $userId = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : 6; // Usamos un ID de prueba, ajustar según corresponda
                        
                        // Incluir archivo de conexión a la base de datos
                        $connection_paths = [
                            "../../C-EDU/conexion.php",
                            "../../config/conexion.php",
                            "../config/conexion.php",
                            "conexion.php"
                        ];

                        $conn = null;
                        foreach ($connection_paths as $path) {
                            if (file_exists($path)) {
                                include $path;
                                if (isset($conn)) {
                                    break;
                                }
                            }
                        }

                        // Si aún no hay conexión, crear una
                        if (!isset($conn)) {
                            $servername = "localhost";
                            $username = "root";
                            $password = "";
                            $dbname = "cedu";

                            // Create connection
                            $conn = new mysqli($servername, $username, $password, $dbname);
                            
                            // Check connection
                            if ($conn->connect_error) {
                                echo "<img src='avatar.png'>"; // Mostrar imagen predeterminada en caso de error
                                echo "<!-- Error de conexión: " . $conn->connect_error . " -->";
                            }
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
                            
                            // Obtener la información del usuario
                            $userInfo = [];
                            $query = "SELECT * FROM usuario LEFT JOIN institucion ON usuario.id_institucion = institucion.id_institucion WHERE usuario.id_usuario = ?";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("i", $userId);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0) {
                                $userInfo = $result->fetch_assoc();
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
                            <input type="text" id="nombre" value="<?php echo isset($userInfo['nombre_usuario']) ? $userInfo['nombre_usuario'] : 'Antonio Jose Rengifo Abonia'; ?>" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                            
                            <label>Email</label>
                            <input type="text" id="email" value="<?php echo isset($userInfo['email_usuario']) ? $userInfo['email_usuario'] : 'antoniojrequejo@comfandi.edu.co'; ?>" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                            
                            <label>Fecha de Nacimiento</label>
                            <input type="text" id="fecha_nacimiento" value="16 de Enero de 2001" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                            
                            <label>Materia</label>
                            <input type="text" id="materia" value="Matemáticas" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                            
                            <label>Colegio</label>
                            <input type="text" id="colegio" value="<?php echo isset($userInfo['nombre_institucion']) ? $userInfo['nombre_institucion'] : 'Colegio Comfandi Calipso'; ?>" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                        </div>

                        <div class="columna">
                            <label>Nombre de Usuario</label>
                            <input type="text" id="username" value="antonio.reginfo.2" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                            
                            <label>Contraseña</label>
                            <input type="password" id="password" value="************" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                            
                            <label>Teléfono</label>
                            <input type="text" id="telefono" value="<?php echo isset($userInfo['telefono_usuario']) ? $userInfo['telefono_usuario'] : '+57 315 6162888'; ?>" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                            
                            <label>Grupo a Cargo</label>
                            <input type="text" id="grupo" value="11-B" <?php echo isset($_GET['edit']) ? '' : 'disabled'; ?>>
                        </div>
                    </div>
                    <div class="buttons-container" style="display: flex; justify-content: center; margin-top: 20px;">
                        <?php if(!isset($_GET['edit'])): ?>
                            <a href="?edit=true" class="action-btn edit">Editar Información</a>
                        <?php else: ?>
                            <button type="button" id="save-btn" class="action-btn save">Guardar</button>
                            <button type="button" id="cancel-btn" class="action-btn cancel">Cancelar</button>
                        <?php endif; ?>
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