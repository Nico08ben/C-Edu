<?php

// Incluir el archivo de conexión a la base de datos
// Ajusta la ruta según la estructura de tu proyecto
require_once(__DIR__ . '/../../config/database.php');

// Obtener el filtro de la URL si existe
$filter = $_GET['filter'] ?? ''; // 'created_by_me', 'finished', or '' for all

// Variables para almacenar el statement y el resultado
$stmt = null;
$result = null;
$query_successful = false; // Bandera para rastrear si la consulta fue exitosa

?>
            <div class="admin-controls">
                <div class="filter-dropdown">
                    <label for="task-filter">Filtrar Tareas:</label>
                    <select id="task-filter">
                        <option value="" <?php echo ($filter === '') ? 'selected' : ''; ?>>Todas las Tareas</option>
                        <option value="created_by_me" <?php echo ($filter === 'created_by_me') ? 'selected' : ''; ?>>
                            Creadas por Mí</option>
                        <option value="finished" <?php echo ($filter === 'finished') ? 'selected' : ''; ?>>Terminadas
                        </option>
                    </select>
                </div>
                <div class="admin-actions">
                <a href="admin_tasks.php?action=create" class="btn-crear-tarea">Crear Nueva Tarea</a>
                </div>
            </div>

            <div class="Tareas">
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th><i class="fa-solid fa-hammer"></i><label> Tareas</label></th>
                                <th><i class="fa-solid fa-user-tie"></i><label> Creador</label></th>
                                <th><i class="fa-solid fa-user"></i><label> Asignado A</label></th>
                                <th><i class="fa-solid fa-sliders"></i><label> Estado</label></th>
                                <th><label>Acciones</label></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($conn) { // Verificar si la conexión $conn existe
                                $current_user_id = $_SESSION['id_usuario']; // Obtener el ID del admin actual
                            
                                // Consulta base para obtener tareas
                                $sql = "SELECT t.id_tarea, t.instruccion_tarea, u_creador.nombre_usuario AS nombre_creador, u_asignado.nombre_usuario AS nombre_asignado, t.estado_tarea
                                        FROM tarea t
                                        INNER JOIN usuario u_creador ON t.id_asignador = u_creador.id_usuario
                                        INNER JOIN usuario u_asignado ON t.id_usuario = u_asignado.id_usuario";

                                // Modificar la consulta según el filtro seleccionado
                                $params = [];
                                $types = "";

                                if ($filter === 'created_by_me') {
                                    $sql .= " WHERE t.id_asignador = ?";
                                    $params[] = $current_user_id;
                                    $types = "i"; // 'i' para integer
                                } elseif ($filter === 'finished') {
                                    $sql .= " WHERE t.estado_tarea = 'Completada'";
                                    // No se necesitan parámetros adicionales para este filtro
                                }
                                // Si $filter es '', la consulta base ya muestra todas las tareas
                            
                                // Preparar y ejecutar la consulta
                                if (!empty($params)) {
                                    $stmt = $conn->prepare($sql);
                                    if ($stmt) {
                                        $stmt->bind_param($types, ...$params);
                                        if ($stmt->execute()) {
                                            $result = $stmt->get_result();
                                            $query_successful = true; // La consulta preparada fue exitosa
                                            // --- CERRAR STATEMENT INMEDIATAMENTE DESPUÉS DE get_result() ---
                                            $stmt->close();
                                            $stmt = null; // Establecer a null para evitar intentos de cierre posteriores
                                            // --- FIN CERRAR STATEMENT ---
                                        } else {
                                            echo "<tr><td colspan='5'>Error al ejecutar la consulta preparada: " . $stmt->error . "</td></tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5'>Error al preparar la consulta filtrada: " . $conn->error . "</td></tr>";
                                    }
                                } else {
                                    // Ejecutar consulta simple si no hay parámetros (filtro 'finished' o sin filtro)
                                    $result = $conn->query($sql);
                                    if ($result) {
                                        $query_successful = true; // La consulta simple fue exitosa
                                    } else {
                                        echo "<tr><td colspan='5'>Error al ejecutar la consulta: " . $conn->error . "</td></tr>";
                                    }
                                }

                                // Mostrar resultados si la consulta fue exitosa y hay filas
                                if ($query_successful && $result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['instruccion_tarea']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nombre_creador']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['nombre_asignado']) . "</td>"; // Mostrar nombre del asignado
                                        echo "<td>" . htmlspecialchars($row['estado_tarea']) . "</td>";
                                        // El data-task-id se usará en JavaScript
                                        // El botón de detalles ahora apunta a la versión de detalles para Admin
                                        echo "<td><button class='btn-datalles' data-task-id='" . $row['id_tarea'] . "'>Detalles</button></td>";
                                        echo "</tr>";
                                    }
                                } elseif ($query_successful && $result && $result->num_rows === 0) {
                                    echo "<tr><td colspan='5'>No hay tareas disponibles con el filtro seleccionado.</td></tr>"; // Mensaje si no hay resultados con filtro
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
                                echo "<tr><td colspan='5'>Error: No se pudo establecer la conexión a la base de datos.</td></tr>"; // Colspan ajustado
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
                    window.location.href = `admin_tasks.php?action=details&id_tarea=${taskId}`;
                }, 600);
            });
        });

        // Script para el filtro desplegable
        const filterDropdown = document.getElementById("task-filter");
        filterDropdown.addEventListener("change", (e) => {
            const selectedFilter = e.target.value;
            // Redirigir a la misma página con el parámetro de filtro en la URL
            window.location.href = `admin_tasks.php?filter=${selectedFilter}`;
        });
    </script>