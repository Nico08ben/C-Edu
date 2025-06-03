<?php
// Chat/send_message.php

// It's good practice to put error reporting at the very top for debugging,
// but REMOVE or COMMENT OUT for a production environment.
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

// Check if user is logged in
if (!isset($_SESSION['id_usuario'])) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

// Include database connection
include '../../conexion.php'; // Correct path: D:\xampp\htdocs\C-Edu\conexion.php

// Check database connection
if ($conn->connect_error) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    error_log("Connection failed in send_message.php: " . $conn->connect_error);
    echo json_encode(['success' => false, 'message' => 'Error: Database connection failed. Details: ' . $conn->connect_error]);
    exit;
}

// Set charset
$conn->set_charset("utf8");

// Set content type to JSON for the response
// This should be called as early as possible, but after session_start and includes.
if (!headers_sent()) {
    header('Content-Type: application/json');
}

// Get input data
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Entrada inválida (Invalid input).']);
    exit;
}

$senderId = $_SESSION['id_usuario'];
$receiverId = $input['receiver_id'] ?? null;
$messageContent = $input['message'] ?? null;
$isFilePlaceholder = isset($input['is_file_placeholder']) && $input['is_file_placeholder'] === true;

// Validate required fields
if (empty($receiverId)) {
    echo json_encode(['success' => false, 'message' => 'Falta el ID del receptor (Missing receiver ID).']);
    exit;
}

if (!isset($messageContent) && !$isFilePlaceholder) {
    echo json_encode(['success' => false, 'message' => 'Falta el contenido del mensaje (Missing message content).']);
    exit;
}

if (isset($messageContent) && $messageContent === "" && !$isFilePlaceholder) {
    echo json_encode(['success' => false, 'message' => 'El contenido del mensaje de texto no puede estar vacío (Text message content cannot be empty).']);
    exit;
}

// Prepare SQL for inserting message
$sql = "INSERT INTO mensaje (id_emisor, id_receptor, contenido_mensaje, fecha_envio, leido) VALUES (?, ?, ?, NOW(), 0)";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("iis", $senderId, $receiverId, $messageContent);
    if ($stmt->execute()) {
        $newMessageId = $stmt->insert_id;
        $db_timestamp = date('Y-m-d H:i:s'); // Default timestamp

        // Try to get the exact timestamp from the database for the new message
        $ts_sql = "SELECT fecha_envio FROM mensaje WHERE id_mensaje = ?";
        $ts_stmt = $conn->prepare($ts_sql);
        if ($ts_stmt) {
            $ts_stmt->bind_param("i", $newMessageId);
            if ($ts_stmt->execute()) {
                $ts_result = $ts_stmt->get_result();
                if ($ts_result && $ts_result->num_rows > 0) {
                    $db_timestamp_row = $ts_result->fetch_assoc();
                    $db_timestamp = $db_timestamp_row['fecha_envio'];
                } else {
                    // Log if somehow the inserted message isn't found immediately (should be rare)
                    error_log("send_message.php: Could not find message ID " . $newMessageId . " to fetch timestamp after insert.");
                }
            } else {
                error_log("send_message.php: Failed to execute timestamp fetch for ID " . $newMessageId . ": " . $ts_stmt->error);
            }
            $ts_stmt->close();
        } else {
            error_log("send_message.php: Failed to prepare timestamp fetch: " . $conn->error);
        }

        // Construct sender's photo URL for the response
        $webRootPath = '/C-edu/'; // Define your application's web root path
        // Define the full path to the default avatar
        $defaultAvatarPath = $webRootPath . 'uploads/profile_pictures/default-avatar.png';

        $senderPhotoUrlForResponse = $defaultAvatarPath; // Assume default first
        // $_SESSION['foto_perfil_url'] should store the path relative to the web root's uploads directory
        // e.g., 'uploads/profile_pictures/avatar.jpg'
        if (isset($_SESSION['foto_perfil_url']) && !empty($_SESSION['foto_perfil_url']) && is_string($_SESSION['foto_perfil_url'])) {
            $senderPhotoUrlForResponse = $webRootPath . htmlspecialchars($_SESSION['foto_perfil_url']);
        }

        // Prepare the successful response data
        $responseData = [
            'success' => true,
            'message_data' => [
                'id_mensaje' => $newMessageId,
                'id_emisor' => $senderId,
                'id_receptor' => intval($receiverId),
                'contenido_mensaje' => $messageContent,
                'fecha_envio' => $db_timestamp,
                'leido' => 0,
                'emisor_foto_url' => $senderPhotoUrlForResponse
            ]
        ];

        // Encode and echo the response
        $jsonResponse = json_encode($responseData);
        if ($jsonResponse === false) {
            // Log the data that failed to encode for debugging
            error_log("JSON Encode Error in send_message.php: " . json_last_error_msg() . " - Data: " . print_r($responseData, true));
            // Echo a valid JSON error message
            echo json_encode(['success' => false, 'message' => 'Server error: Failed to encode JSON response. Reason: ' . json_last_error_msg()]);
        } else {
            echo $jsonResponse;
        }

    } else {
        error_log("Error in send_message.php al ejecutar INSERT: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Error al enviar el mensaje (execute): ' . $stmt->error]);
    }
    $stmt->close();
} else {
    error_log("Error in send_message.php al preparar INSERT: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta de envío (prepare): ' . $conn->error]);
}

$conn->close();
?>