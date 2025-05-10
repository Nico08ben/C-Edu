<?php
session_start(); // Debe ser lo primero

// Verificar si el usuario está logueado y es un docente
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 1) { // Rol 1 para Docente
    header("Location: index.php"); // Redirección antes de cualquier salida
    exit;
}

$page_title = "Calendario - Docente";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../src/includes/docente_head.php'; ?>
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/docente_sidebar.css"> <?php // Ya debería estar en docente_head.php si es común ?>
    <link rel="stylesheet" href="assets/css/docente_calendario.css"> <?php // Nuevo CSS específico ?>
</head>

<body>
    <?php require_once __DIR__ . '/../src/includes/docente_sidebar.php'; ?>
    
    <section class="home">
        <div class="main-content"> <?php // Contenedor general para el contenido de la página ?>
            <div class="header"> <?php // Encabezado con título y user_header ?>
                <h1 id="titulo1-header"><?= htmlspecialchars($page_title) ?></h1>
                <?php require_once __DIR__ . '/../src/includes/user_header.php'; ?>
            </div>

            <div class="container-calendario"> <?php // Contenedor específico para la funcionalidad del calendario ?>
                <header class="header-calendario-controles">
                    <div class="header-left">
                        <button id="today-btn" class="today-btn">
                            <i class="fas fa-calendar-day"></i> Hoy
                        </button>
                        <div class="navigation-buttons">
                            <button id="prev-btn" class="nav-btn"><i class="fas fa-chevron-left"></i></button>
                            <button id="next-btn" class="nav-btn"><i class="fas fa-chevron-right"></i></button>
                        </div>
                        <div id="month-year" class="month-year"></div>
                    </div>
                    <?php /* El user_info.php original ya está cubierto por user_header.php 
                          <div class="header-right">
                               <?php // include '../../PHP/user_info.php'; ?>
                          </div> 
                    */ ?>
                </header>
                
                <div class="action-bar">
                    <button id="new-event-btn" class="new-event-btn">
                        <i class="fas fa-plus"></i> NUEVO EVENTO
                    </button>
                    <div class="view-options">
                        <button id="day-view" class="view-btn">Día</button>
                        <button id="week-view" class="view-btn">Semana</button>
                        <button id="month-view" class="view-btn active">Mes</button>
                    </div>
                </div>
                
                <div class="calendar-container">
                    <div class="mini-calendar" id="mini-calendar"></div>
                    <div class="main-calendar">
                        <div id="calendar"></div>
                    </div>
                </div>
            </div> <?php // Fin de container-calendario ?>
        </div> <?php // Fin de main-content ?>
    </section>

    <div id="event-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal-btn">&times;</span>
            <h2 id="event-title-modal"></h2>
            <p><i class="fas fa-clock"></i> <span id="event-date-modal"></span></p>
            <p><i class="fas fa-align-left"></i> <span id="event-description-modal"></span></p>
            <button id="delete-event-btn" class="btn-danger">
                <i class="fas fa-trash"></i> Eliminar
            </button>
             <button id="edit-event-btn-modal" class="btn-edit">
                <i class="fas fa-edit"></i> Editar
            </button>
        </div>
    </div>

    <div id="new-event-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal-btn">&times;</span>
            <h2 id="form-event-title-modal"><i class="fas fa-calendar-plus"></i> Nuevo Evento</h2>
            <form id="event-form">
                <input type="hidden" id="event-id" name="event-id">
                <div class="form-group">
                    <label for="event-name-input"><i class="fas fa-heading"></i> Título:</label>
                    <input type="text" id="event-name-input" required>
                </div>
                <div class="form-group">
                    <label for="event-start-input"><i class="fas fa-calendar-day"></i> Inicio:</label>
                    <input type="datetime-local" id="event-start-input" required>
                </div>
                <div class="form-group">
                    <label for="event-end-input"><i class="fas fa-calendar-times"></i> Fin:</label>
                    <input type="datetime-local" id="event-end-input">
                </div>
                <div class="form-group">
                    <label for="event-color-input"><i class="fas fa-palette"></i> Color:</label>
                    <select id="event-color-input">
                        <option value="#3eb489">Verde Esmeralda (Por defecto)</option>
                        <option value="#54a0ff">Azul Brillante</option>
                        <option value="#9c27b0">Morado Amatista</option>
                        <option value="#ff9800">Naranja Ámbar</option>
                        <option value="#e74c3c">Rojo Alizarina</option>
                        <option value="#f1c40f">Amarillo Girasol</option>
                        <option value="#3498db">Azul Pedro</option>
                        <option value="#1abc9c">Turquesa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="event-description-input"><i class="fas fa-align-left"></i> Descripción:</label>
                    <textarea id="event-description-input" rows="3"></textarea>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </form>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
    <script src="assets/js/docente_calendario.js"></script> <?php // Nuevo JS específico ?>
</body>
</html>