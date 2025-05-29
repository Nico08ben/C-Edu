<?php
session_start();

// Definición de la ruta al avatar predeterminado.
// ¡IMPORTANTE! Ajusta esta ruta para que apunte a tu imagen de avatar predeterminada.
// Por ejemplo: define('DEFAULT_AVATAR_PATH', '../../assets/images/default-avatar.png');
// o una URL completa: define('DEFAULT_AVATAR_PATH', 'https://tusitio.com/imagenes/avatar_predeterminado.png');
if (!defined('DEFAULT_AVATAR_PATH')) {
    define('DEFAULT_AVATAR_PATH', '../../assets/images/default_avatar.png'); // ¡Ajusta esta ruta!
}

$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}
include '../../conexion.php'; // Aquí se establece la conexión $conn
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
                        // Aseguramos que $id_docente esté definido
                        if (isset($_SESSION['id_usuario'])) {
                            $id_docente = $_SESSION['id_usuario'];
                            $sql_tareas = "SELECT
                                    t.instruccion_tarea AS clase,
                                    u.nombre_usuario AS asignado_por,
                                    t.fecha_fin_tarea AS fecha_limite
                                FROM
                                    tarea t
                                JOIN
                                    usuario u ON t.id_asignador = u.id_usuario
                                WHERE
                                    t.id_usuario = $id_docente
                                ORDER BY
                                    t.fecha_fin_tarea DESC
                                LIMIT 3";
                            $result_tareas = $conn->query($sql_tareas);
                            if ($result_tareas && $result_tareas->num_rows > 0) {
                                while ($row_tarea = $result_tareas->fetch_assoc()) {
                                    echo '<div class="task-item">';
                                    echo '<div>' . htmlspecialchars($row_tarea['clase']) . '</div>';
                                    echo '<div class="task-details">';
                                    echo 'Asignado por: ' . htmlspecialchars($row_tarea['asignado_por']) . '<br>';
                                    echo 'Fecha Límite: ' . htmlspecialchars($row_tarea['fecha_limite']);
                                    echo '</div>';
                                    echo '<span class="red-dot"></span>';
                                    echo '</div>';
                                }
                            } else {
                                echo '<div>No hay tareas asignadas.</div>';
                            }
                        } else {
                            echo '<div>Error: No se pudo identificar al docente.</div>';
                        }
                        ?>
                    </div>
                    <a href="/C-EDU/Docente/Tareas Asignadas/index.php" style="text-decoration: none;"><button
                            class="btn-ingresar">INGRESAR</button></a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-calendar"></i> Eventos Pendientes
                    </div>
                    <div class="div-card">
                        <?php
                        $sql_eventos = "SELECT
                                asignacion_evento AS nombre_evento
                            FROM
                                evento
                            WHERE
                                fecha_evento >= CURDATE()
                            ORDER BY
                                fecha_evento ASC
                            LIMIT 3";
                        $result_eventos = $conn->query($sql_eventos);
                        if ($result_eventos && $result_eventos->num_rows > 0) {
                            while ($row_evento = $result_eventos->fetch_assoc()) {
                                echo '<div class="event-item">';
                                echo '<div>' . htmlspecialchars($row_evento['nombre_evento']) . '</div>';
                                echo '<span><i class="fa-solid fa-arrow-right"></i></span>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div>No hay eventos pendientes.</div>';
                        }
                        ?>
                    </div>
                    <a href="/C-EDU/Docente/Calendario/index.php" style="text-decoration: none;"><button
                            class="btn-ingresar">INGRESAR</button></a>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-comment"></i> Comunicación
                    </div>
                    <div class="div-card">
                        <?php
                        if (isset($_SESSION['id_usuario'])) {
                            // $id_docente ya debería estar definido desde el inicio del script.
                        
                            echo '<ul class="content-messages-list">';

                            // NUEVA CONSULTA SQL
                            $sql_users = "SELECT
                                            u_interlocutor.id_usuario,
                                            u_interlocutor.nombre_usuario AS fullName,
                                            u_interlocutor.foto_perfil_url,
                                            m_actual_last.contenido_mensaje AS lastMessageContent,
                                            m_actual_last.fecha_envio AS last_message_date
                                        FROM
                                            (
                                                SELECT
                                                    interlocutor_id,
                                                    MAX(fecha_envio_conv) AS max_fecha_conversacion
                                                FROM (
                                                    SELECT id_receptor AS interlocutor_id, fecha_envio AS fecha_envio_conv FROM mensaje WHERE id_emisor = $id_docente
                                                    UNION ALL
                                                    SELECT id_emisor AS interlocutor_id, fecha_envio AS fecha_envio_conv FROM mensaje WHERE id_receptor = $id_docente
                                                ) AS conversaciones_con_docente
                                                WHERE interlocutor_id != $id_docente
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
                                                    (id_emisor = $id_docente AND id_receptor = interlocutores_recientes.interlocutor_id) OR
                                                    (id_emisor = interlocutores_recientes.interlocutor_id AND id_receptor = $id_docente)
                                                ORDER BY fecha_envio DESC
                                                LIMIT 1
                                            )
                                        ORDER BY
                                            m_actual_last.fecha_envio DESC";

                            $result_users = $conn->query($sql_users);

                            if ($result_users && $result_users->num_rows > 0) {
                                while ($user = $result_users->fetch_assoc()) {
                                    $userPhoto = (!empty($user['foto_perfil_url'])) ? htmlspecialchars($user['foto_perfil_url']) : DEFAULT_AVATAR_PATH;
                                    $userName = htmlspecialchars($user['fullName']);
                                    $userId = htmlspecialchars($user['id_usuario']);
                                    $lastMessage = !empty($user['lastMessageContent']) ? htmlspecialchars($user['lastMessageContent']) : 'Conversación iniciada.'; // Mensaje alternativo
// ...dentro del bucle while ($user = $result_users->fetch_assoc())
                                    // $userPhoto, $userName, $userId ya están definidos
                        
                                    // Define la URL base de tu página de chat
                                    $chatPageUrl = "/C-EDU/Docente/Chat/index.php";

                                    // Prepara los parámetros para la URL
                                    // http_build_query se encargará de la codificación URL adecuada
                                    $linkParamsArray = [
                                        'userId' => $userId,
                                        'userName' => $userName,
                                        'userFoto' => $userPhoto
                                    ];
                                    $queryString = http_build_query($linkParamsArray);
                                    $fullLink = $chatPageUrl . '?' . $queryString;

                                    echo '<li>';
                                    // El href ahora apunta a la página de chat con los parámetros del usuario
                                    // Los atributos data-* se pueden mantener si tienes otra lógica JS en la Home que los use,
                                    // pero para esta navegación específica, la información viaja por la URL.
                                    echo '    <a href="' . htmlspecialchars($fullLink) . '" 
                                                    data-user-id="' . $userId . '"
                                                    data-user-name="' . $userName . '"
                                                    data-user-foto="' . $userPhoto . '">';
                                    echo '        <img class="content-message-image" src="' . $userPhoto . '" alt="' . $userName . '">';
                                    echo '        <span class="content-message-info">';
                                    echo '            <span class="content-message-name">' . $userName . '</span>';
                                    echo '            <span class="content-message-text">' . $lastMessage . '</span>'; // Asumiendo que $lastMessage ya lo tienes
                                    echo '        </span>';
                                    echo '        <span class="content-message-more">';
                                    echo '            <span class="content-message-unread"></span>';
                                    echo '            <span class="content-message-time"></span>';
                                    echo '        </span>';
                                    echo '    </a>';
                                    echo '</li>';
                                    // ...
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
                    <a href="/C-EDU/Docente/Chat/index.php" style="text-decoration: none;"><button
                            class="btn-ingresar">INGRESAR</button></a>
                </div>
            </div>
        </main>
    </section>
</body>

</html>