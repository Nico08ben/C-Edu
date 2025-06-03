<?php
// Ubicación: /Administrador/Chat/send_message.php o similar

// ini_set('display_errors', 1); // Descomentar para depuración
// ini_set('display_startup_errors', 1); // Descomentar para depuración
// error_reporting(E_ALL); // Descomentar para depuración

session_start();

// 1. Verificar autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado.']);
    exit;
}

// 2. Incluir archivos necesarios
// Conexión a la base de datos (ajusta la ruta si es necesario)
require_once __DIR__ . '/../../conexion.php';
// Función para crear notificaciones (ajusta la ruta si es necesario)
// Si send_message.php está en /C-Edu/Administrador/Chat/ y crear_notificacion.php está en /C-Edu/PHP/api/
require_once __DIR__ . '/../../PHP/api/crear_notificacion.php'; // ¡Verifica esta ruta!

// 3. Verificar conexión a la BD
if ($conn->connect_error) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    error_log("Error de conexión en send_message.php: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos. Detalles: ' . $conn->connect_error]);
    exit;
}

// Establecer charset para la conexión
$conn->set_charset("utf8");

// 4. Establecer encabezado de respuesta JSON (hacerlo lo antes posible)
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// 5. Obtener y validar datos de entrada
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Entrada JSON inválida.']);
    exit;
}

$senderId = (int)$_SESSION['id_usuario']; // ID del remitente (usuario logueado)
$receiverId = isset($input['receiver_id']) ? (int)$input['receiver_id'] : null; // ID del destinatario
$messageContent = $input['message'] ?? null; // Contenido del mensaje

if (empty($receiverId)) {
    echo json_encode(['success' => false, 'message' => 'ID del destinatario no proporcionado.']);
    exit;
}

if ($messageContent === null || $messageContent === "") {
    echo json_encode(['success' => false, 'message' => 'El contenido del mensaje no puede estar vacío.']);
    exit;
}

// 6. Insertar el mensaje en la base de datos
$sqlInsertMsg = "INSERT INTO mensaje (id_emisor, id_receptor, contenido_mensaje, fecha_envio, leido) VALUES (?, ?, ?, NOW(), 0)";
$stmtInsertMsg = $conn->prepare($sqlInsertMsg);

if ($stmtInsertMsg) {
    $stmtInsertMsg->bind_param("iis", $senderId, $receiverId, $messageContent);
    if ($stmtInsertMsg->execute()) {
        $newMessageId = $stmtInsertMsg->insert_id;
        $db_timestamp = date('Y-m-d H:i:s'); // Usar NOW() es mejor, pero podemos obtenerla si es necesario

        // Opcional: Obtener el timestamp exacto de la BD si es crucial
        $ts_sql = "SELECT fecha_envio FROM mensaje WHERE id_mensaje = ?";
        $ts_stmt = $conn->prepare($ts_sql);
        if ($ts_stmt) {
            $ts_stmt->bind_param("i", $newMessageId);
            if ($ts_stmt->execute()) {
                $ts_result = $ts_stmt->get_result();
                if ($ts_result && $ts_result->num_rows > 0) {
                    $db_timestamp_row = $ts_result->fetch_assoc();
                    $db_timestamp = $db_timestamp_row['fecha_envio'];
                }
            }
            $ts_stmt->close();
        }

        // ----- INICIO: LÓGICA PARA CREAR NOTIFICACIÓN -----
        $nombreEmisor = $_SESSION['nombre_usuario'] ?? 'Alguien'; // Nombre del remitente
        $tipoNotificacionParaDb = 'nuevo_mensaje_chat'; // Tipo de notificación para la BD

        // Crear un mensaje de notificación descriptivo
        $mensajeParaNotificacion = '';
        if (preg_match('/\.(jpeg|jpg|gif|png|webp)$/i', $messageContent) && (strpos($messageContent, '/C-edu/uploads/profile_pictures/') === 0 || strpos($messageContent, 'uploads/profile_pictures/') === 0) ) {
            $mensajeParaNotificacion = $nombreEmisor . ' te envió una imagen.';
        } elseif (preg_match('/^(http|https):\/\/[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/i', $messageContent) && preg_match('/giphy\.com/i', $messageContent)) {
             $mensajeParaNotificacion = $nombreEmisor . ' te envió un sticker.';
        } elseif (strpos($messageContent, 'blob:http') === 0) {
            $mensajeParaNotificacion = $nombreEmisor . ' te envió un mensaje de voz.';
        } else {
            $previewContenido = htmlspecialchars(substr(strip_tags($messageContent), 0, 30));
            if (strlen(strip_tags($messageContent)) > 30) {
                $previewContenido .= '...';
            }
            $mensajeParaNotificacion = $nombreEmisor . ' dice: "' . $previewContenido . '"';
        }

        // Construir el enlace para la notificación
        // Este enlace debe llevar al chat del usuario RECEPTOR, abriendo la conversación con el EMISOR.
        $fotoEmisorParaURL = ''; // Foto del EMISOR del mensaje
        // $_SESSION['foto_perfil_url'] es la foto del usuario logueado (el emisor)
        if (isset($_SESSION['foto_perfil_url']) && !empty($_SESSION['foto_perfil_url'])) {
            // Asegurarse que la URL sea absoluta desde la raíz del sitio web
            $fotoEmisorParaURL = '/C-edu/' . ltrim(htmlspecialchars($_SESSION['foto_perfil_url']), '/');
        } else {
            $fotoEmisorParaURL = '/C-edu/uploads/profile_pictures/default-avatar.png';
        }

        // Determinar la ruta base del chat para el RECEPTOR de la notificación
        // Esto es importante si Admin y Docente tienen rutas de chat diferentes
        $baseChatPathParaReceptor = '';
        $rolReceptorId = null;

        // Consultar el rol del RECEPTOR
        $stmtRolReceptor = $conn->prepare("SELECT id_rol FROM usuario WHERE id_usuario = ?");
        if ($stmtRolReceptor) {
            $stmtRolReceptor->bind_param("i", $receiverId);
            if ($stmtRolReceptor->execute()) {
                $resultRolReceptor = $stmtRolReceptor->get_result();
                if ($filaRolReceptor = $resultRolReceptor->fetch_assoc()) {
                    $rolReceptorId = (int)$filaRolReceptor['id_rol'];
                }
            }
            $stmtRolReceptor->close();
        }

        if ($rolReceptorId === 0) { // 0 para Admin
            $baseChatPathParaReceptor = '/C-edu/Administrador/Chat/';
        } elseif ($rolReceptorId === 1) { // 1 para Docente
            $baseChatPathParaReceptor = '/C-edu/Docente/Chat/';
        } else {
            // Fallback: si no se puede determinar el rol del receptor o es otro rol
            // Asumir una ruta por defecto o manejar el error. Aquí usamos Docente como fallback.
            // ¡IMPORTANTE! Ajusta esto si tienes más roles o una estructura diferente.
            $baseChatPathParaReceptor = '/C-edu/Docente/Chat/';
            // Podrías también registrar un aviso: error_log("No se pudo determinar la ruta del chat para el receptor ID: $receiverId con rol ID: $rolReceptorId");
        }
        
        $enlaceNotificacion = $baseChatPathParaReceptor . 'index.php?userId=' . $senderId . '&userName=' . urlencode($nombreEmisor) . '&userFoto=' . urlencode($fotoEmisorParaURL);

        // Llamar a la función para crear la notificación
        // El destinatario de la notificación es $receiverId
        if (!crearNotificacion($conn, $receiverId, $tipoNotificacionParaDb, $mensajeParaNotificacion, $enlaceNotificacion)) {
            error_log("send_message.php: Falló la creación de notificación para el usuario $receiverId (mensaje de $senderId).");
            // No es necesario detener el flujo principal si la notificación falla, pero sí registrarlo.
        }
        // ----- FIN: LÓGICA PARA CREAR NOTIFICACIÓN -----

        // Preparar datos del remitente para la respuesta del mensaje actual
        $webRootPath = '/C-edu/';
        $defaultAvatarPath = $webRootPath . 'uploads/profile_pictures/default-avatar.png';
        $senderPhotoUrlForResponse = $defaultAvatarPath;

        // $_SESSION['foto_perfil_url'] es la foto del usuario logueado (el emisor)
        if (isset($_SESSION['foto_perfil_url']) && !empty($_SESSION['foto_perfil_url']) && is_string($_SESSION['foto_perfil_url'])) {
            $senderPhotoUrlForResponse = $webRootPath . ltrim(htmlspecialchars($_SESSION['foto_perfil_url']), '/');
        }

        $responseData = [
            'success' => true,
            'message_data' => [
                'id_mensaje' => $newMessageId,
                'id_emisor' => $senderId, // Quién envió este mensaje
                'id_receptor' => $receiverId, // Quién lo recibió
                'contenido_mensaje' => $messageContent,
                'fecha_envio' => $db_timestamp, // Timestamp del mensaje
                'leido' => 0, // Estado inicial del mensaje
                'emisor_foto_url' => $senderPhotoUrlForResponse // Foto del emisor de este mensaje
            ]
        ];

        $jsonResponse = json_encode($responseData);
        if ($jsonResponse === false) {
            error_log("Error de codificación JSON en send_message.php: " . json_last_error_msg() . " - Datos: " . print_r($responseData, true));
            echo json_encode(['success' => false, 'message' => 'Error del servidor: Fallo al codificar respuesta JSON. Razón: ' . json_last_error_msg()]);
        } else {
            echo $jsonResponse;
        }

    } else {
        error_log("Error en send_message.php al ejecutar INSERT: " . $stmtInsertMsg->error);
        echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje (fallo en ejecución): ' . $stmtInsertMsg->error]);
    }
    $stmtInsertMsg->close();
} else {
    error_log("Error en send_message.php al preparar INSERT: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de envío (fallo en preparación): ' . $conn->error]);
}

// 7. Cerrar la conexión a la BD
$conn->close();
?>