<?php
session_start();
/*
if (!isset($_SESSION['id_usuario'])) {
    header("Location: C-Edu/index.php");
    exit();
}*/
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "../../SIDEBAR/Docente/head.php" ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Inicio</title>
    <link rel="stylesheet" href="inciodocentess.css">
</head>
<body>
    <?php include "../../SIDEBAR/Docente/sidebar.php" ?>

    <section class="home">
    <div class="header">
                <h1 id="titulo1-header">Bienvenido a C-EDU</h1>
                <?php include '../../PHP/user_info.php'; ?>
            </div>
        <main class="main-content">
        
            <div class="cards-container">
                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-file"></i> Tareas Asignadas
                    </div>
                <div class="div-card">
                    <div class="task-item">
                        <div>Clase 8B - Asistir</div>
                        <div class="task-details">
                            Asignado por: Coordinador
                            <br>
                            Fecha Límite: 04/01/2024
                        </div>
                        <span class="red-dot"></span>
                    </div>
                </div>
                    <button class="btn-ingresar">INGRESAR</button>
                </div>
    
                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-calendar"></i></i> Eventos Pendientes
                    </div>
                    <div class="div-card">
                        <div class="event-item">
                            <div>Reunión Padres de Familia</div>
                            <span><i class="fa-solid fa-arrow-right"></i></span>
                        </div>
                        <div class="event-item">
                            <div>Reunión de docentes</div>
                            <span><i class="fa-solid fa-arrow-right"></i></span>
                        </div>

                    </div>
                    
                    <button class="btn-ingresar">INGRESAR</button>
                </div>
    
                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-comment"></i> Comunicación
                    </div>
                    <div class="div-card">
                        <div class="chat-item">
                            <i class="fa-solid fa-user"></i>
                            <div class="chat-content">
                                <div class="chat-name">Mario</div>
                                <div class="chat-role">Docente Lenguaje</div>
                                <div class="chat-message">ESCRIBIENDO...</div>
                            </div>
                            <div class="status-dot green"></div>
                        </div>
                        <div class="chat-item">
                            <i class="fa-solid fa-user"></i>
                            <div class="chat-content">
                                <div class="chat-name">Diana</div>
                                <div class="chat-role">Docente sociales</div>
                                <div class="chat-message">¿Ya entregaste el formulario?</div>
                            </div>
                            <div class="status-dot orange"></div>
                        </div>
                        <div class="chat-item">
                            <i class="fa-solid fa-user"></i>
                            <div class="chat-content">
                                <div class="chat-name">Miguel</div>
                                <div class="chat-role">Docente Física</div>
                                <div class="chat-message">Hola ¿cómo estás?</div>
                            </div>
                            <div class="status-dot.gray"></div>
                        </div>

                    </div>
                    
                    <button class="btn-ingresar">INGRESAR</button>
                </div>
            </div>
        </main>
    </section>
    

</body>
</html>