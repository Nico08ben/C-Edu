<?php
session_start();

// RUTA ÚNICA Y CORRECTA para el avatar por defecto
// ¡Asegúrate de que esta ruta sea la misma que usas en el resto de tu aplicación!
if (!defined('DEFAULT_AVATAR_PATH')) {
    define('DEFAULT_AVATAR_PATH', '/C-edu/uploads/profile_pictures/default-avatar.png'); // <-- EJEMPLO DE RUTA UNIFICADA
}

$webRootPath = '/C-edu/';

$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}
include '../../conexion.php';
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo $theme_class; ?>">

<head>
    <?php include "../../SIDEBAR/Docente/head.php" ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                        <?php
                        // No necesitas $id_Admin aquí a menos que quieras filtrar por tareas creadas por el admin
                        // $id_Admin = $_SESSION['id_usuario']; // Ajusta según tu sesión
                        
                        // MODIFICACIÓN: Incluir t.id_tarea en la consulta para el administrador
                        $sql = "SELECT
                                    t.id_tarea, -- Añadido id_tarea aquí
                                    t.instruccion_tarea AS clase,
                                    u_asignador.nombre_usuario AS asignado_por,
                                    u_asignado.nombre_usuario AS asignado_a,
                                    t.fecha_fin_tarea AS fecha_limite
                                FROM
                                    tarea t
                                JOIN
                                    usuario u_asignador ON t.id_asignador = u_asignador.id_usuario
                                JOIN
                                    usuario u_asignado ON t.id_usuario = u_asignado.id_usuario
                                ORDER BY
                                    t.fecha_fin_tarea DESC
                                LIMIT 3";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                // Construir la URL para los detalles de la tarea
                                // Asumo que el administrador tiene una página de detalles similar
                                // o que las tareas de los docentes se ven desde la página de detalles del docente.
                                // Si hay una página de detalles específica para el administrador, ajusta la ruta.
                                // Para este ejemplo, usaré la misma lógica de TareasDetalles.php que subiste.
                                $task_detail_url = $webRootPath . 'Administrador/Tareas%20Asignadas/TareasDetalles.php?id_tarea=' . htmlspecialchars($row['id_tarea']);

                                echo '<a href="' . $task_detail_url . '" class="task-item-link" style="text-decoration: none; color: inherit;">'; // Enlace para todo el div
                                echo '<div class="task-item">';
                                echo '<div>' . htmlspecialchars($row['clase']) . '</div>';
                                echo '<div class="task-details">';
                                echo 'Asignado por: ' . htmlspecialchars($row['asignado_por']) . '<br>';
                                echo 'Asignado a: ' . htmlspecialchars($row['asignado_a']) . '<br>'; // Mostrar también a quién fue asignada
                                echo 'Fecha Límite: ' . htmlspecialchars($row['fecha_limite']);
                                echo '</div>';
                                echo '<span class="red-dot"></span>';
                                echo '</div>';
                                echo '</a>'; // Cerrar el enlace
                            }
                        } else {
                            echo '<div>No hay tareas asignadas.</div>';
                        }
                        ?>
                    </div>
                    <a href="/C-EDU/Administrador/Tareas%20Asignadas/index.php" style="text-decoration: none;"><button class="btn-ingresar">INGRESAR</button></a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-calendar"></i> Eventos Pendientes
                    </div>
                    <div class="div-card">
                        <?php
                        // 1. Asegurarnos de que el usuario ha iniciado sesión
                        if (isset($_SESSION['id_usuario'])) {

                            // 2. Obtenemos el ID del docente actual de forma segura
                            $id_docente_eventos = (int)$_SESSION['id_usuario'];

                            // 3. Consulta SQL MODIFICADA para filtrar por el usuario actual
                            // Se une la tabla 'evento' con 'usuario_evento'
                            $sql_eventos = "SELECT e.id_evento, e.titulo_evento, e.descripcion_evento, e.fecha_evento
                            FROM evento e
                            JOIN usuario_evento ue ON e.id_evento = ue.id_evento
                            WHERE ue.id_usuario = {$id_docente_eventos}
                              AND e.fecha_evento >= CURDATE()
                            ORDER BY e.fecha_evento ASC
                            LIMIT 3";

                            $result_eventos = $conn->query($sql_eventos);

                            if ($result_eventos && $result_eventos->num_rows > 0) {
                                while ($row_evento = $result_eventos->fetch_assoc()) {
                                    $event_link = '';
                                    $baseCalendarUrl = '/C-EDU/Docente/Calendario/index.php';
                                    $event_link = $baseCalendarUrl . '?id_evento=' . htmlspecialchars($row_evento['id_evento']);


                                    $displayDate = '';
                                    if (!empty($row_evento['fecha_evento'])) {
                                        try {
                                            $dateObj = new DateTime($row_evento['fecha_evento']);
                                            if (extension_loaded('intl')) {
                                                $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'd MMM');
                                                $formattedMonth = str_replace('.', '', $formatter->format($dateObj));
                                                $parts = explode(' ', $formattedMonth);
                                                $parts[count($parts) - 1] = ucfirst($parts[count($parts) - 1]);
                                                $displayDate = implode(' ', $parts);
                                            } else {
                                                $displayDate = $dateObj->format('d M');
                                            }
                                        } catch (Exception $e) {
                                            error_log("Error parsing event date: " . $row_evento['fecha_evento'] . " - " . $e->getMessage());
                                            $displayDate = date('d M', strtotime($row_evento['fecha_evento']));
                                        }
                                    }

                                    echo '<a href="' . $event_link . '" class="event-item-link" style="text-decoration: none; color: inherit;">';
                                    echo '<div class="event-item">';
                                    echo '<div>';
                                    echo '<strong>' . htmlspecialchars($row_evento['titulo_evento']) . '</strong><br>';
                                    echo '<small>';

                                    $preview = mb_substr($row_evento['descripcion_evento'], 0, 30);
                                    if (mb_strlen($row_evento['descripcion_evento']) > 30) {
                                        $preview .= "...";
                                    }
                                    if (!empty($preview)) {
                                        echo htmlspecialchars($preview);
                                    }

                                    if (!empty($displayDate)) {
                                        if (!empty($preview)) echo ' - ';
                                        echo htmlspecialchars($displayDate);
                                    }
                                    echo '</small>';
                                    echo '</div>';
                                    echo '<span><i class="fa-solid fa-arrow-right"></i></span>';
                                    echo '</div>';
                                    echo '</a>';
                                }
                            } else {
                                // Mensaje más específico si no hay eventos para este usuario
                                echo '<div>No tienes eventos pendientes asignados.</div>';
                            }
                        } else {
                            echo '<div>Error: No se pudo identificar al usuario para cargar los eventos.</div>';
                        }
                        ?>
                    </div>
                    <a href="/C-EDU/Docente/Calendario/index.php" style="text-decoration: none;"><button class="btn-ingresar">INGRESAR</button></a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-comment"></i> Comunicación
                    </div>
                    <div class="div-card">
                        <?php
                        if (isset($_SESSION['id_usuario'])) {
                            $id_docente_com = $_SESSION['id_usuario'];

                            echo '<ul class="content-messages-list">';

                            // SQL MODIFICADA para incluir unreadCount
                            $sql_users = "SELECT
                                                u_interlocutor.id_usuario,
                                                u_interlocutor.nombre_usuario AS fullName,
                                                u_interlocutor.foto_perfil_url,
                                                m_actual_last.contenido_mensaje AS lastMessageContent,
                                                m_actual_last.fecha_envio AS last_message_date,
                                                (
                                                    SELECT COUNT(*)
                                                    FROM mensaje unread_m
                                                    WHERE unread_m.id_emisor = u_interlocutor.id_usuario
                                                      AND unread_m.id_receptor = {$id_docente_com}
                                                      AND unread_m.leido = 0
                                                ) AS unreadCount
                                            FROM
                                                (
                                                    SELECT
                                                        interlocutor_id,
                                                        MAX(fecha_envio_conv) AS max_fecha_conversacion
                                                    FROM (
                                                        SELECT id_receptor AS interlocutor_id, fecha_envio AS fecha_envio_conv FROM mensaje WHERE id_emisor = {$id_docente_com}
                                                        UNION ALL
                                                        SELECT id_emisor AS interlocutor_id, fecha_envio AS fecha_envio_conv FROM mensaje WHERE id_receptor = {$id_docente_com}
                                                    ) AS conversaciones_con_docente
                                                    WHERE interlocutor_id != {$id_docente_com}
                                                    GROUP BY interlocutor_id
                                                    ORDER BY max_fecha_conversacion DESC
                                                    LIMIT 3
                                                ) AS interlocutores_recientes
                                            JOIN
                                                usuario u_interlocutor ON interlocutores_recientes.interlocutor_id = u_interlocutor.id_usuario
                                            JOIN
                                                mensaje m_actual_last ON m_actual_last.id_mensaje = (
                                                    SELECT id_mensaje
                                                    FROM mensaje
                                                    WHERE
                                                        (id_emisor = {$id_docente_com} AND id_receptor = interlocutores_recientes.interlocutor_id) OR
                                                        (id_emisor = interlocutores_recientes.interlocutor_id AND id_receptor = {$id_docente_com})
                                                    ORDER BY fecha_envio DESC
                                                    LIMIT 1
                                                )
                                            ORDER BY
                                                m_actual_last.fecha_envio DESC";

                            $result_users = $conn->query($sql_users);

                            if ($result_users && $result_users->num_rows > 0) {
                                while ($user = $result_users->fetch_assoc()) {
                                    $userPhotoUrl = '';
                                    if (!empty($user['foto_perfil_url'])) {
                                        $userPhotoUrl = $webRootPath . htmlspecialchars($user['foto_perfil_url']);
                                    } else {
                                        $userPhotoUrl = DEFAULT_AVATAR_PATH;
                                    }

                                    $userName = htmlspecialchars($user['fullName']);
                                    $userId = htmlspecialchars($user['id_usuario']);

                                    $lastMessageRaw = $user['lastMessageContent'] ?? '';
                                    $lastMessagePreview = 'Conversación iniciada.';
                                    if (!empty($lastMessageRaw)) {
                                        if (preg_match('/\.(jpeg|jpg|gif|png|webp)$/i', $lastMessageRaw) || strpos(strtolower($lastMessageRaw), 'uploads/') === 0) {
                                            $lastMessagePreview = '[Imagen]';
                                        } elseif (strpos(strtolower($lastMessageRaw), 'http://') === 0 || strpos(strtolower($lastMessageRaw), 'https://') === 0) {
                                            $lastMessagePreview = '[Sticker]';
                                        } elseif (strpos(strtolower($lastMessageRaw), 'blob:http') === 0) {
                                            $lastMessagePreview = '[Mensaje de voz]';
                                        } else {
                                            $tempPreview = mb_substr($lastMessageRaw, 0, 25);
                                            if (mb_strlen($lastMessageRaw) > 25) {
                                                $tempPreview .= "...";
                                            }
                                            $lastMessagePreview = htmlspecialchars($tempPreview);
                                        }
                                    }

                                    // Procesar unreadCount
                                    $unreadCount = isset($user['unreadCount']) ? intval($user['unreadCount']) : 0;
                                    $unreadIndicatorHTML = '';
                                    if ($unreadCount > 0) {
                                        $unreadIndicatorHTML = '<span class="content-message-unread">' . $unreadCount . '</span>';
                                    } else {
                                        // Para la barra verde si no hay no leídos (requiere CSS para .is-indicator-bar)
                                        $unreadIndicatorHTML = '<span class="content-message-unread is-indicator-bar"></span>';
                                    }

                                    // Procesar y formatear last_message_date
                                    $lastMessageTimeOutput = '';
                                    if (!empty($user['last_message_date'])) {
                                        try {
                                            $dateObj = new DateTime($user['last_message_date']);
                                            $today = new DateTime('today'); // Para comparar si es hoy

                                            if ($dateObj->format('Y-m-d') == $today->format('Y-m-d')) {
                                                // Formato: HH:MM p.m./a.m. (ej: 07:24<br>p.m.)
                                                $timeStr = $dateObj->format('h:i');
                                                $ampm = $dateObj->format('a'); // 'am' o 'pm'
                                                $lastMessageTimeOutput = $timeStr . '<br>' . $ampm;
                                            } else {
                                                // Formato: DD-Mes (ej: 27-<br>may)
                                                $day = $dateObj->format('d');
                                                $month = '';
                                                // Para obtener el mes en español (requiere extensión intl o un array)
                                                if (extension_loaded('intl')) {
                                                    $formatter = new IntlDateFormatter('es_CO', IntlDateFormatter::NONE, IntlDateFormatter::NONE, null, null, 'MMM');
                                                    $month = str_replace('.', '', strtolower($formatter->format($dateObj))); // quita el punto si lo hay
                                                } else {
                                                    $month = strtolower($dateObj->format('M')); // Fallback: 'Jan', 'Feb', etc.
                                                }
                                                $lastMessageTimeOutput = $day . '-<br>' . $month;
                                            }
                                        } catch (Exception $e) {
                                            error_log("Error parsing date in Inicio/index.php: " . $user['last_message_date'] . " - " . $e->getMessage());
                                        }
                                    }

                                    $chatPageUrl = "/C-EDU/Docente/Chat/index.php";
                                    $linkParamsArray = [
                                        'userId' => $userId,
                                        'userName' => $userName,
                                        'userFoto' => $userPhotoUrl
                                    ];
                                    $queryString = http_build_query($linkParamsArray);
                                    $fullLink = $chatPageUrl . '?' . $queryString;

                                    echo '<li>';
                                    echo '    <a href="' . htmlspecialchars($fullLink) . '" 
                                                      data-user-id="' . $userId . '"
                                                      data-user-name="' . $userName . '"
                                                      data-user-foto="' . htmlspecialchars($userPhotoUrl) . '">';
                                    echo '        <img class="content-message-image" src="' . htmlspecialchars($userPhotoUrl) . '" alt="' . $userName . '">';
                                    echo '        <span class="content-message-info">';
                                    echo '            <span class="content-message-name">' . $userName . '</span>';
                                    echo '            <span class="content-message-text">' . $lastMessagePreview . '</span>'; // $lastMessagePreview ya está escapado si es texto
                                    echo '        </span>';
                                    echo '        <span class="content-message-more">';
                                    echo              $unreadIndicatorHTML; // Indicador de no leídos
                                    echo '            <span class="content-message-time">' . $lastMessageTimeOutput . '</span>'; // Hora/Fecha formateada (contiene <br>)
                                    echo '        </span>';
                                    echo '    </a>';
                                    echo '</li>';
                                }
                            } else {
                                echo '<li>No hay conversaciones recientes.</li>';
                            }

                            echo '</ul>';
                        } else {
                            echo '<div>No se pudo cargar la lista de usuarios (requiere iniciar sesión).</div>';
                        }
                        ?>
                    </div>
                    <a href="/C-EDU/Docente/Chat/index.php" style="text-decoration: none;"><button class="btn-ingresar">INGRESAR</button></a>
                </div>
            </div>
        </main>
    </section>
</body>

</html>