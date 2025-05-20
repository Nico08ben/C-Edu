<?php
// sidebar.php
// This assumes session_start() has been called in an earlier script if this is an include.
// If sidebar.php is one of the first files processed for a page, you might need session_start() here.
// if (session_status() == PHP_SESSION_NONE) {
//    session_start();
// }

$loggedInUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'guest';
?>

<nav class="sidebar close">
    <header>
        <div class="image-text">
            <span class="image">
                <a href="../Home/index.php"><img src="/C-Edu/SIDEBAR/Admin/cedu.png" alt="logo"></a>
            </span>
            <div class="text header-text">
                <span class="name">C-EDU</span>
                <span class="profession">Web Developer</span>
            </div>
        </div>
        <i class='bx bx-chevron-right toggle'></i>
    </header>
    <div class="menu-bar">
        <div class="menu">
            <li class="search-box">
                <i class='bx bx-search-alt icon'></i>
                <input type="text" placeholder="Search...">
            </li>
            <ul class="menu-links">
                <li class="nav-links">
                    <a href="/C-Edu/Administrador/Chat/index.php">
                        <i class='bx bx-conversation icon'></i>
                        <span class="text nav-text">Mensajes</span>
                    </a>
                </li>
                <li class="nav-links">
                    <a href="/C-Edu/Administrador/Tareas asignadas/index.php">
                        <i class='bx bx-book-open icon'></i>
                        <span class="text nav-text">Tareas</span>
                    </a>
                </li>
                <li class="nav-links">
                    <a href="/C-Edu/Administrador/Calendario/index.php">
                        <i class='bx bx-notepad icon'></i>
                        <span class="text nav-text">Calendario</span>
                    </a>
                </li>
                <li class="nav-links">
                    <a href="/C-Edu/Administrador/Edit_User/index.php">
                        <i class='bx bx-user icon'></i>
                        <span class="text nav-text">Modificar Usuarios</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="bottom-content">
            <li class="">
                <a href="/C-Edu/index.php">
                    <i class='bx bx-log-out icon'></i>
                    <span class="text nav-text">Cerrar Sesion</span>
                </a>
            </li>
            <li class="mode">
                <div class="moon-sun">
                    <i class='bx bx-moon icon moon'></i>
                    <i class='bx bx-sun icon sun'></i>
                </div>
                <span class="mode-text text">Modo Oscuro</span>
                <div class="toggle-switch">
                    <span class="switch"></span>
                </div>
            </li>
        </div>
    </div>
</nav>

<script>
    const currentUserId = "<?php echo htmlspecialchars($loggedInUserId, ENT_QUOTES, 'UTF-8'); ?>";
</script>
<script src="/C-Edu/SIDEBAR/Admin/java.js"></script>