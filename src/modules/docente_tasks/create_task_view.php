<?php
require_once(__DIR__ . '/../../config/database.php');

$mensaje = ''; // Variable para mostrar mensajes de éxito o error
$stmt_insert = null; // Inicializar statement de inserción a null

// Procesar el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario de forma segura
    $instruccion = $_POST['instruccion_tarea'] ?? '';
    $fecha_fin = $_POST['fecha_fin_tarea'] ?? '';
    $prioridad = $_POST['prioridad'] ?? '';

    $id_usuario_asignado = $_SESSION['id_usuario']; // El docente actual se asigna la tarea a sí mismo
    $id_asignador = $_SESSION['id_usuario']; // El docente actual es también el asignador

    // Validar que los campos obligatorios no estén vacíos
    if (empty($instruccion) || empty($fecha_fin) || empty($prioridad)) {
        $mensaje = "Error: Todos los campos son obligatorios.";
    } else {
        if ($conn) {
            // Preparar la consulta SQL para insertar la nueva tarea
            // El id_usuario (asignado) y el id_asignador son el ID del docente actual
            $sql_insert = "INSERT INTO tarea (instruccion_tarea, id_usuario, id_asignador, fecha_inicio_tarea, fecha_fin_tarea, estado_tarea, prioridad, porcentaje_avance) VALUES (?, ?, ?, CURDATE(), ?, 'Pendiente', ?, 0)";
            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert) {
                // Vincular parámetros a la consulta preparada
                $stmt_insert->bind_param("siiss", $instruccion, $id_usuario_asignado, $id_asignador, $fecha_fin, $prioridad);

                // Ejecutar la consulta
                if ($stmt_insert->execute()) {
                    $mensaje = "Tarea creada exitosamente.";
                    // Opcional: Redirigir a la lista de tareas del docente después de crear
                    header('Location: docente_tasks.php');
                    exit();
                } else {
                    $mensaje = "Error al crear la tarea: " . $stmt_insert->error;
                }
            } else {
                $mensaje = "Error al preparar la consulta de inserción: " . $conn->error;
            }
        } else {
            $mensaje = "Error: No se pudo establecer la conexión a la base de datos.";
        }
    }
}

// --- INICIO: Lógica de cierre de statement mejorada ---
// Cerrar statement de inserción si fue preparado y es un objeto válido
if ($stmt_insert instanceof mysqli_stmt) {
    $stmt_insert->close();
}
// Establecer statement de inserción a null después de usarlo
$stmt_insert = null;
// --- FIN: Lógica de cierre de statement mejorada ---


// $conn->close(); // Cerrar conexión si no se necesita más en este script
?>
   <div class="main-content">
             <div class="header">
                <h1 id="titulo1-header">DOCENTE - CREAR NUEVA TAREA</h1> 
            </div>

            <div class="create-task-container">
                <h2>Ingresar Detalles de la Tarea</h2>
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo (strpos($mensaje, 'Error') !== false || strpos($mensaje, 'obligatorios') !== false) ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                <form action="docente_tasks.php?action=store_task" method="post">
                    <div class="form-group">
                        <label for="instruccion_tarea">Instrucción de la Tarea:</label>
                        <textarea id="instruccion_tarea" name="instruccion_tarea" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin_tarea">Fecha de Fin:</label>
                        <input type="date" id="fecha_fin_tarea" name="fecha_fin_tarea" required>
                    </div>
                     <div class="form-group">
                        <label for="prioridad">Prioridad:</label>
                        <select id="prioridad" name="prioridad" required>
                            <option value="Baja">Baja</option>
                            <option value="Media">Media</option>
                            <option value="Alta">Alta</option>
                        </select>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit-task">Crear Tarea</button>
                        <a href="docente_tasks.php" class="btn-cancel">Cancelar</a> </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
