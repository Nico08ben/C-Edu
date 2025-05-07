<?php
require_once(__DIR__ . '/../../config/database.php');

$mensaje = ''; // Variable para mostrar mensajes de éxito o error
$stmt_insert = null; // Inicializar statement de inserción a null
$result_users = null; // Inicializar resultado de usuarios a null

// Procesar el formulario cuando se envía por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario de forma segura
    $instruccion = $_POST['instruccion_tarea'] ?? '';
    $id_usuario_asignado = $_POST['id_usuario_asignado'] ?? '';
    $fecha_fin = $_POST['fecha_fin_tarea'] ?? '';
    $prioridad = $_POST['prioridad'] ?? '';

    $id_asignador = $_SESSION['id_usuario']; // El administrador actual es quien asigna la tarea

    // Validar que los campos obligatorios no estén vacíos
    if (empty($instruccion) || empty($id_usuario_asignado) || empty($fecha_fin) || empty($prioridad)) {
        $mensaje = "Error: Todos los campos son obligatorios.";
    } else {
        if ($conn) {
            // Preparar la consulta SQL para insertar la nueva tarea
            $sql_insert = "INSERT INTO tarea (instruccion_tarea, id_usuario, id_asignador, fecha_inicio_tarea, fecha_fin_tarea, estado_tarea, prioridad, porcentaje_avance) VALUES (?, ?, ?, CURDATE(), ?, 'Pendiente', ?, 0)";
            $stmt_insert = $conn->prepare($sql_insert);

            if ($stmt_insert) {
                // Vincular parámetros a la consulta preparada
                $stmt_insert->bind_param("siiss", $instruccion, $id_usuario_asignado, $id_asignador, $fecha_fin, $prioridad);

                // Ejecutar la consulta
                if ($stmt_insert->execute()) {
                    $mensaje = "Tarea creada exitosamente.";
                    header('Location: admin_tasks.php');
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

// Obtener la lista de usuarios para el selector en el formulario
$users = [];
if ($conn) {
    $sql_users = "SELECT id_usuario, nombre_usuario FROM usuario ORDER BY nombre_usuario ASC"; // Ordenar por nombre para mejor UX en el desplegable
    $result_users = $conn->query($sql_users);
    if ($result_users) {
        if ($result_users->num_rows > 0) {
            while ($row_user = $result_users->fetch_assoc()) {
                $users[] = $row_user;
            }
        }
        $result_users->free(); // Liberar el resultado
    } else {
         // Manejar error si la consulta de usuarios falla
         $mensaje .= (empty($mensaje) ? "" : "<br>") . "Error al obtener la lista de usuarios: " . $conn->error;
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
<section class="home">
        <div class="main-content">
             <div class="header">
                <h1 id="titulo1-header">ADMIN - CREAR NUEVA TAREA</h1> <?php include '../../PHP/user_info.php'; // Reutilizando user_info.php ?>
            </div>

            <div class="create-task-container">
                <h2>Ingresar Detalles de la Tarea</h2>
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo (strpos($mensaje, 'Error') !== false || strpos($mensaje, 'obligatorios') !== false) ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                <form action="admin_tasks.php?action=store_task" method="post">
                    <div class="form-group">
                        <label for="instruccion_tarea">Instrucción de la Tarea:</label>
                        <textarea id="instruccion_tarea" name="instruccion_tarea" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="id_usuario_asignado">Asignar a Usuario:</label>
                        <select id="id_usuario_asignado" name="id_usuario_asignado" required style="width: 100%;">
                            <option value="">-- Seleccione un usuario --</option>
                            <?php foreach ($users as $user): ?>
                                <option value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                                    <?php echo htmlspecialchars($user['nombre_usuario']); ?></option>
                            <?php endforeach; ?>
                        </select>
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
                        <a href="admin_tasks.php" class="btn-cancel">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/i18n/es.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar Select2 en el select de usuarios
            $('#id_usuario_asignado').select2({
                placeholder: "-- Seleccione un usuario --", // Texto del placeholder
                allowClear: true, // Permite borrar la selección
                language: "es" // Usar localización en español si se incluyó el archivo i18n/es.js
            });
        });
    </script>
