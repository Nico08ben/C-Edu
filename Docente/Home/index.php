<?php
session_start();
$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}
include '../../conexion.php'; ?>
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
                        $id_docente = $_SESSION['id_usuario']; // Ajusta según tu sesión
                        $sql = "SELECT
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
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="task-item">';
                                echo '<div>' . htmlspecialchars($row['clase']) . '</div>';
                                echo '<div class="task-details">';
                                echo 'Asignado por: ' . htmlspecialchars($row['asignado_por']) . '<br>';
                                echo 'Fecha Límite: ' . htmlspecialchars($row['fecha_limite']);
                                echo '</div>';
                                echo '<span class="red-dot"></span>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div>No hay tareas asignadas.</div>';
                        }
                        ?>
                    </div>
                    <button class="btn-ingresar">INGRESAR</button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-calendar"></i> Eventos Pendientes
                    </div>
                    <div class="div-card">
                        <?php
                        $sql = "SELECT
                                    asignacion_evento AS nombre_evento
                                FROM
                                    evento
                                WHERE
                                    fecha_evento >= CURDATE()
                                ORDER BY
                                    fecha_evento ASC
                                LIMIT 3";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo '<div class="event-item">';
                                echo '<div>' . htmlspecialchars($row['nombre_evento']) . '</div>';
                                echo '<span><i class="fa-solid fa-arrow-right"></i></span>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div>No hay eventos pendientes.</div>';
                        }
                        ?>
                    </div>
                    <button class="btn-ingresar">INGRESAR</button>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="fa-regular fa-comment"></i> Comunicación
                    </div>
                    <div class="div-card">
                        <?php
                        $sql = "SELECT
                                    u_emisor.nombre_usuario AS nombre,
                                    r.tipo_rol AS rol,
                                    m.mensaje,
                                    m.fecha_mensaje -- Necesaria para ordenar, aunque no se muestre directamente en esta parte del HTML
                                FROM
                                    mensaje m
                                JOIN
                                    usuario u_emisor ON m.id_emisor = u_emisor.id_usuario
                                JOIN
                                    rol r ON u_emisor.id_rol = r.id_rol
                                WHERE
                                    m.id_receptor = $id_docente -- Mostrar mensajes donde el docente actual es el receptor
                                ORDER BY
                                    m.fecha_mensaje DESC
                                LIMIT 3";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $status_class = $row['estado'] == 'online' ? 'green' : ($row['estado'] == 'ausente' ? 'orange' : 'gray');
                                echo '<div class="chat-item">';
                                echo '<i class="fa-solid fa-user"></i>';
                                echo '<div class="chat-content">';
                                echo '<div class="chat-name">' . htmlspecialchars($row['nombre']) . '</div>';
                                echo '<div class="chat-role">' . htmlspecialchars($row['rol']) . '</div>';
                                echo '<div class="chat-message">' . htmlspecialchars($row['mensaje']) . '</div>';
                                echo '</div>';
                                echo '<div class="status-dot ' . $status_class . '"></div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div>No hay mensajes recientes.</div>';
                        }
                        ?>
                    </div>
                    <button class="btn-ingresar">INGRESAR</button>
                </div>
            </div>
        </main>
    </section>
</body>

</html>