<?php
session_start();
// Redirigir si el usuario no ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../../index.php'); // Ajusta la ruta
    exit();
}

require_once(__DIR__ . '/../../conexion.php');

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
                    // header('Location: index.php');
                    // exit();
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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DOCENTE - Crear Tarea</title> <?php include "../../SIDEBAR/Docente/head.php"; ?> <link rel="stylesheet" href="tareascss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* Estilos específicos para el formulario de creación de tarea */
        .create-task-container {
            background-color: var(--sidebar-color); /* Usando color de sidebar para el contenedor */
            padding: 25px;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px; /* Limitar ancho máximo para el formulario */
            margin-left: auto;
            margin-right: auto;
        }
        .create-task-container h2 {
            color: var(--primary-color); /* Usando color primario para el título */
            margin-bottom: 20px;
            border-bottom: 2px solid var(--primary-color-ligth); /* Usando color primario ligero para el borde */
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--text-color); /* Usando color de texto */
        }
        .form-group input[type="text"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc; /* Borde gris */
            border-radius: 4px;
            box-sizing: border-box; /* Incluir padding y border en el tamaño total */
            color: var(--text-color); /* Color de texto para inputs */
            background-color: var(--body-color); /* Fondo ligero para inputs */
        }
         body.dark .form-group input[type="text"],
         body.dark .form-group input[type="date"],
         body.dark .form-group select,
         body.dark .form-group textarea {
             background-color: var(--primary-color-ligth); /* Fondo oscuro para inputs en modo oscuro */
             border-color: #555; /* Borde más oscuro en modo oscuro */
             color: var(--text-color); /* Color de texto en modo oscuro */
         }

        .form-group textarea {
            resize: vertical; /* Permitir redimensionamiento vertical */
            min-height: 100px;
        }
        .form-actions {
            margin-top: 20px;
            text-align: right; /* Alinea los botones a la derecha */
        }
        .btn-submit-task {
            padding: 10px 20px;
            background: var(--primary-color); /* Usando color primario */
            color: white;
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: background-color 0.3s ease;
        }
         .btn-submit-task:hover {
            background-color: #d4a738; /* Tono más oscuro para hover */
        }
         .btn-cancel {
            padding: 10px 20px;
            background: #ccc; /* Color gris para cancelar */
            color: var(--title-color); /* Color de título para texto */
            border: none;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
            transition: background-color 0.3s ease;
        }
         .btn-cancel:hover {
            background-color: #bbb; /* Tono más oscuro para hover */
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: .25rem;
        }
         .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: .25rem;
        }
    </style>
</head>
<body>
    <?php include "../../SIDEBAR/Docente/sidebar.php"; ?> <section class="home">
        <div class="main-content">
             <div class="header">
                <h1 id="titulo1-header">DOCENTE - CREAR NUEVA TAREA</h1> <?php include '../../PHP/user_info.php'; ?>
            </div>

            <div class="create-task-container">
                <h2>Ingresar Detalles de la Tarea</h2>
                <?php if ($mensaje): ?>
                    <div class="alert <?php echo (strpos($mensaje, 'Error') !== false || strpos($mensaje, 'obligatorios') !== false) ? 'alert-danger' : 'alert-success'; ?>">
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>
                <form action="CrearTareaDocente.php" method="post">
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
                        <a href="index.php" class="btn-cancel">Cancelar</a> </div>
                </form>
            </div>
        </div>
    </section>
</body>
</html>
