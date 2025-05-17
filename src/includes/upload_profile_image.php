<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../conexion.php'; // Ajusta la ruta a tu archivo de conexión

$response = ['success' => false, 'message' => 'Error desconocido.'];
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $fileType = $_FILES['profile_image']['type'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB

    if (in_array($fileType, $allowedTypes)) {
        if ($_FILES['profile_image']['size'] <= $maxFileSize) {
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            // Crear un nombre de archivo único para evitar colisiones y añadir el ID de usuario
            $newName = 'user_' . $id_usuario . '_' . uniqid() . '.' . $ext;
            
            // Ruta relativa desde la raíz del proyecto para guardar la imagen
            $uploadDir = '../../../public/assets/images/uploads/'; // Asegúrate que esta carpeta exista y tenga permisos de escritura
            
            // Comprobar si el directorio existe, si no, intentar crearlo
            if (!file_exists(__DIR__ . '/../' . $uploadDir)) {
                if (!mkdir(__DIR__ . '/../' . $uploadDir, 0775, true)) {
                    $response['message'] = 'Error: No se pudo crear el directorio de subida.';
                    echo json_encode($response);
                    exit;
                }
            }
            
            $uploadPathOnServer = __DIR__ . '/' . $uploadDir . $newName; // Ruta física en el servidor
            $imageUrlForDb = '/assets/images/uploads/' . $newName; // Ruta que se guardará en la BD

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPathOnServer)) {
                // Imagen guardada en el servidor, ahora actualizamos la BD
                $stmt = $conn->prepare("UPDATE usuario SET foto_perfil_url = ? WHERE id_usuario = ?");
                if ($stmt) {
                    $stmt->bind_param("si", $imageUrlForDb, $id_usuario);
                    if ($stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Imagen de perfil actualizada correctamente.';
                        $response['imageUrl'] = $imageUrlForDb; // Enviar la nueva URL para actualizar la vista previa si es necesario
                        
                        // Actualizar la variable de sesión si la usas para la imagen de perfil
                        $_SESSION['foto_perfil_url'] = $imageUrlForDb;

                    } else {
                        $response['message'] = 'Error al actualizar la base de datos: ' . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $response['message'] = 'Error al preparar la consulta: ' . $conn->error;
                }
            } else {
                $response['message'] = 'Error al mover el archivo subido.';
            }
        } else {
            $response['message'] = 'El archivo es demasiado grande. Máximo 5MB.';
        }
    } else {
        $response['message'] = 'Tipo de archivo no permitido. Sube JPG, PNG, GIF o WEBP.';
    }
} else {
    $response['message'] = 'No se recibió ningún archivo o hubo un error en la subida.';
    if (isset($_FILES['profile_image']['error']) && $_FILES['profile_image']['error'] !== 0) {
        // Proporcionar un mensaje de error más específico si está disponible
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE   => "El archivo excede la directiva upload_max_filesize en php.ini.",
            UPLOAD_ERR_FORM_SIZE  => "El archivo excede la directiva MAX_FILE_SIZE especificada en el formulario HTML.",
            UPLOAD_ERR_PARTIAL    => "El archivo fue solo parcialmente subido.",
            UPLOAD_ERR_NO_FILE    => "No se subió ningún archivo.",
            UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal.",
            UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco.",
            UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la subida del archivo.",
        ];
        $errorCode = $_FILES['profile_image']['error'];
        $response['message'] = $uploadErrors[$errorCode] ?? 'Error desconocido durante la subida del archivo.';
    }
}

$conn->close();
echo json_encode($response);
?>