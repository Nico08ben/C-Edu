<!DOCTYPE html>
<html lang="es">

<head>
    <?php include "../../SIDEBAR/Docente/head.php" ?>
    <link rel="stylesheet" href="chat.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Chat</title>
</head>

<body>
    <?php include "../../SIDEBAR/Docente/sidebar.php" ?>
    <section class="home">
        <div class="container">
            <div class="sidebar-chat">
                <input type="text" placeholder="Buscar" class="search-bar">
                <div class="announcement">
                    <h2>COMUNICADOS GENERALES</h2>
                    <p>Coordinador: Todos los miembros del comité de convivencia deben presentarse en rectoría, informar
                        a los alumnos.</p>
                </div>
                <div class="contact-list">
                    <div class="contact active">
                        <img src="user1.png" alt="Mario">
                        <div>
                            <h3>Mario</h3>
                            <p>Docente Lenguaje<br><span class="status">ESCRIBIENDO...</span></p>
                        </div>
                    </div>
                    <div class="contact">
                        <img src="user2.png" alt="Diana">
                        <div>
                            <h3>Diana</h3>
                            <p>Docente Sociales<br>¿Ya entregaste el formulario?</p>
                        </div>
                    </div>
                    <div class="contact">
                        <img src="user3.png" alt="Miguel">
                        <div>
                            <h3>Miguel</h3>
                            <p>Docente Física<br>Hola ¿cómo estás?</p>
                        </div>
                    </div>
                    <div class="contact">
                        <img src="user4.png" alt="Camilo">
                        <div>
                            <h3>Camilo</h3>
                            <p>Docente Lenguaje<br><span class="status">ESCRIBIENDO...</span></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="chat-window">
                <div class="chat-header">
                    <img src="user1.png" alt="Mario">
                    <h3>Mario</h3>
                    <span class="status">ESCRIBIENDO...</span>
                </div>
                <div class="chat-body">
                    <!-- Aquí irán los mensajes -->
                </div>
                <div class="chat-footer">
                    <input type="text" placeholder="Mensaje...">
                    <button type="submit">Enviar</button>
                </div>
            </div>
        </div>

        <div id="profile-modal" class="modal">
            <div class="modal-content">
                <i class="fa-solid fa-xmark close-modal"></i>
                <img id="profile-pic" src="" alt="Foto de perfil">
                <h2 id="profile-name"></h2>
                <p id="profile-role"></p>
                <p id="profile-status"></p>
            </div>
        </div>

        <script src="script.js"></script>
    </section>
</body>

</html>