<?php
// Incluir el archivo de conexión a la base de datos
require_once(__DIR__ . '/../config/database.php');
?>
    <script>
        // Hacemos el id_usuario disponible globalmente para JavaScript
        const ID_USUARIO_LOGUEADO = <?php echo json_encode($_SESSION['id_usuario']); ?>;
    </script>

                <div class="notificaciones-container">
                <div class="notificaciones-icono" id="notificacionesIcono">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="none" d="M0 0h24v24H0z" />
                        <path d="M20 17h2v2H2v-2h2v-7a8 8 0 1 1 16 0v7zm-2 0v-7a6 6 0 1 0-12 0v7h12zm-9 4h6v2H9v-2z" />
                    </svg>
                    <span class="notificaciones-contador" id="notificacionesContador">0</span>
                </div>
                <div class="notificaciones-dropdown" id="notificacionesDropdown">
                    <div class="notificaciones-dropdown-header">
                        <span>Notificaciones</span>
                    </div>
                    <ul class="notificaciones-lista" id="listaNotificaciones">
                        <li class="sin-notificaciones">Cargando...</li>
                    </ul>
                    <div class="notificaciones-dropdown-footer">
                        <button id="marcarTodasLeidasBtn">Marcar todas como leídas</button>
                    </div>
                </div>
    <script src="/C-Edu/public/assets/js/notificaciones.js"></script>
