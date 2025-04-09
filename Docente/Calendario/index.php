<!DOCTYPE html>
<html lang="es">
<head>
    <?php include "../../SIDEBAR/Docente/head.php" ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <title>Calendario</title>
    
    <!-- FullCalendar CSS -->
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css' rel='stylesheet' />
    <link rel="stylesheet" href="calendario.css">
</head>

<body>
    <?php include "../../SIDEBAR/Docente/sidebar.php" ?>
    <section class="home">
        <div class="container">
            <header>
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
                <div class="header-right">
                    <div class="profile">
                        <i class="fa-regular fa-bell"></i>
                        <div class="profile-info">
                            <h3>Antonio</h3>
                            <p>Docente de Matemáticas</p>
                        </div>
                        <a href="../UserProfile/index.php"><i class="fa-solid fa-user"></i></a>
                    </div>
                </div>
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
        </div>
    </section>

    <!-- Modal para eventos -->
    <div id="event-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="event-title"></h2>
            <p><i class="fas fa-clock"></i> <span id="event-date"></span></p>
            <p><i class="fas fa-align-left"></i> <span id="event-description"></span></p>
            <button id="delete-event" class="btn-danger">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </div>

    <!-- Modal para nuevo evento -->
    <div id="new-event-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2><i class="fas fa-calendar-plus"></i> Nuevo Evento</h2>
            <form id="event-form">
                <div class="form-group">
                    <label for="event-name"><i class="fas fa-heading"></i> Título:</label>
                    <input type="text" id="event-name" required>
                </div>
                <div class="form-group">
                    <label for="event-start"><i class="fas fa-calendar-day"></i> Inicio:</label>
                    <input type="datetime-local" id="event-start" required>
                </div>
                <div class="form-group">
                    <label for="event-end"><i class="fas fa-calendar-times"></i> Fin:</label>
                    <input type="datetime-local" id="event-end">
                </div>
                <div class="form-group">
                    <label for="event-color"><i class="fas fa-palette"></i> Color:</label>
                    <select id="event-color">
                        <option value="#3eb489">Verde</option>
                        <option value="#2196F3">Azul</option>
                        <option value="#9C27B0">Morado</option>
                        <option value="#FF9800">Naranja</option>
                        <option value="#F44336">Rojo</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="event-description"><i class="fas fa-align-left"></i> Descripción:</label>
                    <textarea id="event-description" rows="3"></textarea>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar
                </button>
            </form>
        </div>
    </div>

    <!-- FullCalendar JS -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.js'></script>
    <script src="calendario.js"></script>
</body>
</html>
