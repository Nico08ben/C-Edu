<?php
session_start();
// Redirigir si el usuario no ha iniciado sesión (opcional pero recomendado)
if (!isset($_SESSION['id_usuario'])) {
    // Cambia 'ruta_a_tu_login.php' por la ruta real a tu página de login
    header('Location: ../../index.php');
    exit();
}

// Incluir el archivo de conexión a la base de datos
// Ajusta la ruta según la estructura de tu proyecto
require_once(__DIR__ . '/../../conexion.php');
$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}
// Obtener el filtro de la URL si existe
// Los filtros para el docente serán 'current' (Pendiente/En Proceso) o 'finished' (Terminada/Cancelada/Completada)
$filter = $_GET['filter'] ?? 'current'; // Valor por defecto: 'current'

// Variables para almacenar el statement y el resultado
$stmt = null;
$result = null;
$query_successful = false; // Bandera para rastrear si la consulta fue exitosa

?>
<!DOCTYPE html>
<html lang="es" class="<?php echo $theme_class;?>">

<head>
    <?php include "../../SIDEBAR/Docente/head.php"; ?>
    <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>TAREAS ASIGNADAS Y CREADAS</title>
</head>

<body>
    <?php include "../../SIDEBAR/Docente/sidebar.php"; ?>

    <section class="home">
            <div class="header">
                <h1 id="titulo1-header">MIS TAREAS</h1> <?php include '../../PHP/user_info.php'; ?>
            </div>
        <div class="main-content">
            <div class="admin-controls">
                <div class="filter-dropdown">
                    <label for="task-filter">Filtrar Tareas:</label>
                    <select id="task-filter">
                        <option value="current" <?php echo ($filter === 'current') ? 'selected' : ''; ?>>Actuales</option>
                        <option value="finished" <?php echo ($filter === 'finished') ? 'selected' : ''; ?>>Terminadas
                        </option>
                    </select>
                </div>
                <div class="admin-actions"> <a href="CrearTareaDocente.php" class="btn-crear-tarea">Crear Nueva
                        Tarea</a>
                </div>
            </div>

            <div class="Tareas">
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-hammer"></i><label> Tareas</label></th>
                                <th><i class="fa-solid fa-user-tie"></i><label> Creador</label></th>
                                <th><i class="fa-solid fa-sliders"></i><label> Estado</label></th>
                                <th><label>Acciones</label></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($conn) { // Verificar si la conexión $conn existe
                                $current_user_id = $_SESSION['id_usuario'];

                                // Consulta base para obtener tareas ASIGNADAS AL USUARIO ACTUAL
                                $sql = "SELECT t.id_tarea, t.instruccion_tarea, u_creador.nombre_usuario AS nombre_creador, t.estado_tarea
                                        FROM tarea t
                                        INNER JOIN usuario u_creador ON t.id_asignador = u_creador.id_usuario
                                        WHERE t.id_usuario = ?"; // Filtra por el usuario asignado (el docente actual)
                            
                                // Modificar la consulta según el filtro seleccionado
                                $params = [$current_user_id];
                                $types = "i"; // 'i' para integer
                            
                                if ($filter === 'current') {
                                    // Tareas Pendientes o En Proceso
                                    $sql .= " AND t.estado_tarea IN ('Pendiente', 'En Proceso')";
                                } elseif ($filter === 'finished') {
                                    // Tareas Terminadas, Canceladas o Completada
                                    $sql .= " AND t.estado_tarea IN ('Terminada', 'Cancelada', 'Completada')"; // <-- AGREGADO 'Completada'
                                }
                                // Si el filtro es 'current' (por defecto), ya se aplica la condición IN ('Pendiente', 'En Proceso')
                            
                                // Preparar y ejecutar la consulta
                                $stmt = $conn->prepare($sql);
                                if ($stmt) {
                                    $stmt->bind_param($types, ...$params);
                                    if ($stmt->execute()) {
                                        $result = $stmt->get_result();
                                        $query_successful = true; // La consulta preparada fue exitosa
                            
                                    } else {
                                        echo "<tr><td colspan='4'>Error al ejecutar la consulta preparada: " . $stmt->error . "</td></tr>";
                                    }
                                    // Cerrar statement inmediatamente después de get_result() si fue exitoso, o después de execute si falló
                                    if ($stmt instanceof mysqli_stmt) {
                                        $stmt->close();
                                    }
                                    $stmt = null; // Establecer a null para evitar intentos de cierre posteriores
                                } else {
                                    echo "<tr><td colspan='4'>Error al preparar la consulta filtrada: " . $conn->error . "</td></tr>";
                                }


                                // Mostrar resultados si la consulta fue exitosa y hay filas
                                if ($query_successful && $result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['instruccion_tarea']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nombre_creador']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['estado_tarea']) . "</td>";
                                        // El data-task-id se usará en JavaScript
                                        // El botón de detalles apunta a la página de detalles (puede ser la misma que la del admin si no hay diferencias)
                                        echo "<td><button class='btn-datalles' data-task-id='" . $row['id_tarea'] . "'>Detalles</button></td>";
                                        echo "</tr>";
                                    }
                                } elseif ($query_successful && $result && $result->num_rows === 0) {
                                    echo "<tr><td colspan='4'>No hay tareas disponibles con el filtro seleccionado.</td></tr>"; // Mensaje si no hay resultados con filtro
                                }
                                // Si $query_successful es false, el mensaje de error ya se mostró
                            
                                // --- INICIO: Lógica de cierre de resultado mejorada ---
                                // Liberar resultado si fue obtenido y es un objeto válido
                                if ($result instanceof mysqli_result) {
                                    $result->free();
                                }
                                // Establecer resultado a null después de usarlo
                                $result = null;
                                // --- FIN: Lógica de cierre de resultado mejorada ---
                            

                                // No cierres $conn aquí si user_info.php también lo necesita.
                                // Se cerrará implícitamente al final del script o puedes cerrarlo explícitamente si es el último uso.
                            } else {
                                echo "<tr><td colspan='4'>Error: No se pudo establecer la conexión a la base de datos.</td></tr>"; // Colspan ajustado
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <script>
        // Script para el botón de detalles (sin cambios)
        const buttons = document.querySelectorAll(".btn-datalles");
        buttons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                const taskId = button.dataset.taskId;
                button.classList.add("animate");
                setTimeout(() => {
                    button.classList.remove("animate");
                    // Asegúrate que la ruta a TareasDetalles.php es correcta para el docente
                    window.location.href = `TareasDetalles.php?id_tarea=${taskId}`;
                }, 600);
            });
        });

        // Script para el filtro desplegable
        const filterDropdown = document.getElementById("task-filter");
        filterDropdown.addEventListener("change", (e) => {
            const selectedFilter = e.target.value;
            // Redirigir a la misma página con el parámetro de filtro en la URL
            window.location.href = `index.php?filter=${selectedFilter}`;
        });
    </script>
</body>

</html>