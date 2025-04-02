<!DOCTYPE html>
    <html lang="es">

    <head>
        <?php include "../../SIDEBAR/Docente/head.php" ?>
        <link rel="stylesheet" href="chat.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
            integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
            <link rel="stylesheet" href="calendario.css">
        <title>Calendario</title>
    </head>

    
    <body>
        <?php include "../../SIDEBAR/Docente/sidebar.php" ?>
        <section class="home">
    <div class="container">
        <header>
            <div class="header-left">
                <button class="today-btn">
                    <span class="calendar-icon">📅</span>
                    Hoy
                </button>
                <div class="navigation-buttons">
                    <button class="nav-btn">&#8249;</button>
                    <button class="nav-btn">&#8250;</button>
                </div>
                <div class="month-year">Agosto 2024</div>
            </div>
            <div class="header-right">
                <button class="notification-btn">
                    🔔
                    <span class="notification-badge">2</span>
                </button>
                <div class="user-profile">
                    <div class="profile-pic">
                        <img src="https://via.placeholder.com/40" alt="Usuario">
                    </div>
                    <div class="user-info">
                        <span class="user-name">Antonio</span>
                        <span class="user-role">Docente de Matemáticas</span>
                    </div>
                </div>
            </div>
        </header>
        
        <div class="action-bar">
            <button class="new-event-btn">NUEVO EVENTO</button>
            <div class="view-options">
                <button class="view-btn">Día</button>
                <button class="view-btn">Semana</button>
                <button class="view-btn active">Mes</button>
            </div>
        </div>
        
        <div class="calendar-container">
            <div class="mini-calendar">
                <h3>Agosto 2024</h3>
                <table>
                    <thead>
                        <tr>
                            <th>D</th>
                            <th>L</th>
                            <th>M</th>
                            <th>M</th>
                            <th>J</th>
                            <th>V</th>
                            <th>S</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td>1</td>
                            <td>2</td>
                            <td>3</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td class="selected">5</td>
                            <td>6</td>
                            <td>7</td>
                            <td>8</td>
                            <td>9</td>
                            <td>10</td>
                        </tr>
                        <tr>
                            <td>11</td>
                            <td>12</td>
                            <td>13</td>
                            <td>14</td>
                            <td>15</td>
                            <td>16</td>
                            <td class="today">17</td>
                        </tr>
                        <tr>
                            <td>18</td>
                            <td>19</td>
                            <td>20</td>
                            <td>21</td>
                            <td>22</td>
                            <td>23</td>
                            <td>24</td>
                        </tr>
                        <tr>
                            <td>25</td>
                            <td>26</td>
                            <td>27</td>
                            <td>28</td>
                            <td>29</td>
                            <td>30</td>
                            <td>31</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="calendar-lists">
                    <div class="calendars-header">
                        <span>Mis calendarios</span> ▼
                    </div>
                    <div class="calendar-item">
                        <div class="checkbox checked"></div>
                        Mi calendario
                    </div>
                    <div class="calendar-item">
                        <div class="checkbox checked"></div>
                        Calendario del colegio
                    </div>
                    <div class="calendar-item">
                        <div class="checkbox"></div>
                        Calendario 11B
                    </div>
                </div>
            </div>
            
            <div class="main-calendar">
                <div class="calendar-header">
                    <h2>Agosto 2024</h2>
                </div>
                <div class="days-header">
                    <div>Lunes</div>
                    <div>Martes</div>
                    <div>Miércoles</div>
                    <div>Jueves</div>
                    <div>Viernes</div>
                    <div>Sábado</div>
                    <div>Domingo</div>
                </div>
                <div class="month-grid">
                    <!-- Primera semana -->
                    <div class="day-cell">
                        <span class="day-number">31</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">01</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">02</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">03</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">04</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">05</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">06</span>
                    </div>
                    
                    <!-- Segunda semana -->
                    <div class="day-cell">
                        <span class="day-number">07</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">08</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">09</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">10</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">11</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">12</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">13</span>
                    </div>
                    
                    <!-- Tercera semana -->
                    <div class="day-cell">
                        <span class="day-number">14</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">15</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">16</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">17</span>
                        <div class="event">Reunión Padres de Familia</div>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">18</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">19</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">20</span>
                    </div>
                    
                    <!-- Cuarta semana -->
                    <div class="day-cell">
                        <span class="day-number">21</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">22</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">23</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">24</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">25</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">26</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">27</span>
                    </div>
                    
                    <!-- Quinta semana -->
                    <div class="day-cell">
                        <span class="day-number">28</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">29</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">30</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">31</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">01</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">02</span>
                    </div>
                    <div class="day-cell">
                        <span class="day-number">03</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    </section>


    <div class="loading-indicator">
    <div class="spinner"></div>
</div>

<script src="googleCalendar.js"></script>
<link rel="stylesheet" href="googleCalendar.css">
</body>
</html>



