<?php
// create_task_view.php

// INICIO DEL BLOQUE DE PROCESAMIENTO (ANTES DE CUALQUIER HTML DE ESTE ARCHIVO)
// session_start(); // NO aquí, ya se inició en docente_tasks.php
require_once(__DIR__ . '/../../config/database.php'); // Necesario para la lógica y $_SESSION

$mensaje_interno = ''; // Mensaje específico para este bloque, no confundir con $mensaje global de la vista

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'store_task') { // Asegurarse que la acción es correcta
    $instruccion = $_POST['instruccion_tarea'] ?? '';
    $fecha_fin = $_POST['fecha_fin_tarea'] ?? '';
    $prioridad = $_POST['prioridad'] ?? '';

    $id_usuario_asignado = $_SESSION['id_usuario'];
    $id_asignador = $_SESSION['id_usuario'];

    if (empty($instruccion) || empty($fecha_fin) || empty($prioridad)) {
        // Usar una variable de sesión para pasar el mensaje si vas a redirigir a 'create'
        // O si te quedas en la misma página (sin redirección en caso de error), $mensaje_interno es suficiente
        $_SESSION['form_error_message'] = "Error: Todos los campos son obligatorios.";
        // No redirigir aquí si quieres mostrar el error en el formulario de creación.
        // Si rediriges, hazlo a la página del formulario:
        // header('Location: docente_tasks.php?action=create');
        // exit();
        $mensaje_interno = "Error: Todos los campos son obligatorios."; // Para mostrar si no hay redirección

    } else {
        if ($conn) {
            $sql_insert = "INSERT INTO tarea (instruccion_tarea, id_usuario, id_asignador, fecha_inicio_tarea, fecha_fin_tarea, estado_tarea, prioridad, porcentaje_avance) VALUES (?, ?, ?, CURDATE(), ?, 'Pendiente', ?, 0)";
            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert) {
                $stmt_insert->bind_param("siiss", $instruccion, $id_usuario_asignado, $id_asignador, $fecha_fin, $prioridad);
                if ($stmt_insert->execute()) {
                    $_SESSION['form_success_message'] = "Tarea creada exitosamente."; // Usar sesión para mensaje post-redirección
                    header('Location: docente_tasks.php'); // Redirige a la lista (o donde quieras)
                    exit(); // MUY IMPORTANTE: detener el script después de la redirección
                } else {
                    $mensaje_interno = "Error al crear la tarea: " . $stmt_insert->error;
                }
                if ($stmt_insert instanceof mysqli_stmt) {
                    $stmt_insert->close();
                }
            } else {
                $mensaje_interno = "Error al preparar la consulta de inserción: " . $conn->error;
            }
        } else {
            $mensaje_interno = "Error: No se pudo establecer la conexión a la base de datos.";
        }
    }
    // Si llegamos aquí después de un POST, significa que hubo un error y no se redirigió.
    // $mensaje_interno contendrá el error.
}
// FIN DEL BLOQUE DE PROCESAMIENTO

// Lógica de visualización (se ejecuta si no hubo redirección o es un GET para 'create')
$page_title = "Crear Nueva Tarea - Docente"; // Título para esta vista
$mensaje_vista = $mensaje_interno; // Usar el mensaje del procesamiento

// Recuperar mensajes de sesión si existen (después de una redirección por error o éxito)
if (isset($_SESSION['form_error_message'])) {
    $mensaje_vista = $_SESSION['form_error_message'];
    unset($_SESSION['form_error_message']); // Limpiar después de mostrar
}
if (isset($_SESSION['form_success_message']) && empty($mensaje_vista)) { // Solo si no hay un error de este intento
    // Este mensaje de éxito usualmente se mostraría en la página a la que se redirigió (ej. index_view.php)
    // Pero si la redirección falla por alguna razón y la lógica continúa aquí, podría ser útil.
    // Sin embargo, es mejor que los mensajes de éxito se muestren en la página de destino de la redirección.
    // Por ahora, lo dejaremos así, pero considera dónde quieres mostrar $form_success_message.
}

?>
<div class="create-task-container">
    <h2>Ingresar Detalles de la Tarea</h2>
    <?php if (!empty($mensaje_vista)): ?>
        <div class="alert <?php echo (strpos($mensaje_vista, 'Error') !== false || strpos($mensaje_vista, 'obligatorios') !== false) ? 'alert-danger' : 'alert-success'; ?>">
            <?php echo htmlspecialchars($mensaje_vista); ?>
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
            <a href="docente_tasks.php" class="btn-cancel">Cancelar</a>
        </div>
    </form>
</div>
<?php
// NO CIERRES </body> o </html> aquí si docente_tasks.php los provee.
// Asumiendo que esta vista es un fragmento que se inserta en docente_tasks.php
// y que docente_tasks.php tiene el </div></section></body></html> final.
// Si create_task_view.php tiene su propio </body></html>, entonces
// docente_tasks.php no debería tener esas etiquetas DESPUÉS de incluir esta vista.
?>