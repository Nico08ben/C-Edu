<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../conexion.php'; // Ajusta la ruta a tu archivo de conexión
// Al principio de tu script PHP (el segundo, el que no funciona)
header('Content-Type: application/json'); // Asegura que el cliente sepa que es JSON

// ...
// Por ejemplo, en la comprobación de GD:
if (!extension_loaded('gd')) {
    $response['success'] = false;
    $response['message'] = 'La extensión GD no está cargada en el servidor.';
    echo json_encode($response);
    exit; // Usa exit después de json_encode
}
if (!function_exists('imagecreatefromjpeg')) { // y para otras funciones
    $response['success'] = false;
    $response['message'] = 'La función imagecreatefromjpeg (u otra) no existe. Revisa la configuración de GD.';
    echo json_encode($response);
    exit;
}

$response = ['success' => false, 'message' => 'Error desconocido.'];
$id_usuario = $_SESSION['id_usuario'] ?? null;

if (!$id_usuario) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit;
}

if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
    $file = $_FILES['profile_image'];
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif', // GIF no se optimiza tan bien con GD en términos de calidad/compresión como jpg/png/webp
        'image/webp' => 'webp'
    ];
    $fileType = $file['type'];
    $maxFileSize = 5 * 1024 * 1024; // 5 MB
    $targetWidth = 200; // Ancho deseado para la foto de perfil
    $targetHeight = 200; // Alto deseado para la foto de perfil

    if (array_key_exists($fileType, $allowedTypes)) {
        if ($file['size'] <= $maxFileSize) {
            $original_tmp_path = $file['tmp_name'];
            $ext = $allowedTypes[$fileType]; // Obtener la extensión correcta basada en el MIME type

            // Renombrado: user_{id_usuario}_timestamp.extension
            // El timestamp ayuda a evitar problemas de caché si el nombre fuera siempre el mismo.
            $timestamp = time();
            $newName = 'user_' . $id_usuario . '_' . $timestamp . '.' . $ext;

            $uploadDir = 'uploads/profile_pictures/';

            if (!file_exists(__DIR__ . '/../' . $uploadDir)) {
                if (!mkdir(__DIR__ . '/../' . $uploadDir, 0775, true)) {
                    $response['message'] = 'Error: No se pudo crear el directorio de subida.';
                    echo json_encode($response);
                    exit;
                }
            }

            $finalUploadPathOnServer = __DIR__ . '/../' . $uploadDir . $newName;
            $imageUrlForDb = $uploadDir . $newName;

            // --- Optimización de Imagen ---
            list($width, $height) = getimagesize($original_tmp_path);
            $src_image = null;

            switch ($fileType) {
                case 'image/jpeg':
                    $src_image = imagecreatefromjpeg($original_tmp_path);
                    break;
                case 'image/png':
                    $src_image = imagecreatefrompng($original_tmp_path);
                    break;
                case 'image/gif':
                    $src_image = imagecreatefromgif($original_tmp_path);
                    break;
                case 'image/webp':
                    $src_image = imagecreatefromwebp($original_tmp_path);
                    break;
            }

            if ($src_image) {
                $aspect_ratio = $width / $height;
                if ($targetWidth / $targetHeight > $aspect_ratio) {
                    $new_width = $targetHeight * $aspect_ratio;
                    $new_height = $targetHeight;
                } else {
                    $new_width = $targetWidth;
                    $new_height = $targetWidth / $aspect_ratio;
                }

                $dst_image = imagecreatetruecolor($new_width, $new_height);

                // Para PNG y GIF, preservar transparencia
                if ($fileType == 'image/png' || $fileType == 'image/gif') {
                    imagealphablending($dst_image, false);
                    imagesavealpha($dst_image, true);
                    $transparent = imagecolorallocatealpha($dst_image, 255, 255, 255, 127);
                    imagefilledrectangle($dst_image, 0, 0, $new_width, $new_height, $transparent);
                }

                imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

                $saveSuccess = false;
                switch ($fileType) {
                    case 'image/jpeg':
                        $saveSuccess = imagejpeg($dst_image, $finalUploadPathOnServer, 75); // Calidad 75%
                        break;
                    case 'image/png':
                        $saveSuccess = imagepng($dst_image, $finalUploadPathOnServer, 6); // Nivel de compresión 0-9 (mayor es más compresión)
                        break;
                    case 'image/gif':
                        // Para GIF, simplemente movemos el original si la optimización es compleja o no deseada
                        // O puedes intentar guardarlo, pero GD no es ideal para GIFs animados.
                        // $saveSuccess = imagegif($dst_image, $finalUploadPathOnServer);
                        // Por simplicidad y para mantener animaciones (si las hubiera), movemos el original redimensionado si es posible,
                        // o solo el original si la redimensión de GIFs es problemática.
                        // Aquí optamos por guardar el $dst_image (que será un solo frame para GIFs animados).
                        $saveSuccess = imagegif($dst_image, $finalUploadPathOnServer);
                        break;
                    case 'image/webp':
                        $saveSuccess = imagewebp($dst_image, $finalUploadPathOnServer, 80); // Calidad 80%
                        break;
                }

                imagedestroy($src_image);
                imagedestroy($dst_image);

                if ($saveSuccess) {
                    // --- Eliminar foto de perfil anterior ---
                    $stmt_get_old_photo = $conn->prepare("SELECT foto_perfil_url FROM usuario WHERE id_usuario = ?");
                    if ($stmt_get_old_photo) {
                        $stmt_get_old_photo->bind_param("i", $id_usuario);
                        $stmt_get_old_photo->execute();
                        $result_old_photo = $stmt_get_old_photo->get_result();
                        if ($old_photo_data = $result_old_photo->fetch_assoc()) {
                            if (!empty($old_photo_data['foto_perfil_url'])) {
                                $old_photo_path = __DIR__ . '/../' . $old_photo_data['foto_perfil_url'];
                                if (file_exists($old_photo_path) && is_writable($old_photo_path)) {
                                    unlink($old_photo_path); // Eliminar el archivo antiguo
                                }
                            }
                        }
                        $stmt_get_old_photo->close();
                    }
                    // --- Fin de eliminar foto anterior ---


                    // Actualizar la base de datos con la nueva ruta
                    $stmt_update_db = $conn->prepare("UPDATE usuario SET foto_perfil_url = ? WHERE id_usuario = ?");
                    if ($stmt_update_db) {
                        $stmt_update_db->bind_param("si", $imageUrlForDb, $id_usuario);
                        if ($stmt_update_db->execute()) {
                            $response['success'] = true;
                            $response['message'] = 'Imagen de perfil actualizada y optimizada correctamente.';
                            $response['imageUrl'] = $imageUrlForDb;
                            $_SESSION['foto_perfil_url'] = $imageUrlForDb;
                        } else {
                            $response['message'] = 'Error al actualizar la base de datos: ' . $stmt_update_db->error;
                            // Si falla la BD, eliminar el archivo recién subido para evitar inconsistencias
                            if (file_exists($finalUploadPathOnServer))
                                unlink($finalUploadPathOnServer);
                        }
                        $stmt_update_db->close();
                    } else {
                        $response['message'] = 'Error al preparar la consulta de actualización: ' . $conn->error;
                        if (file_exists($finalUploadPathOnServer))
                            unlink($finalUploadPathOnServer);
                    }
                } else {
                    $response['message'] = 'Error al guardar la imagen optimizada.';
                }
            } else {
                $response['message'] = 'Error al procesar la imagen (no se pudo crear desde el archivo).';
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
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => "El archivo excede la directiva upload_max_filesize en php.ini.",
            UPLOAD_ERR_FORM_SIZE => "El archivo excede la directiva MAX_FILE_SIZE especificada en el formulario HTML.",
            UPLOAD_ERR_PARTIAL => "El archivo fue solo parcialmente subido.",
            UPLOAD_ERR_NO_FILE => "No se subió ningún archivo.",
            UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal.",
            UPLOAD_ERR_CANT_WRITE => "No se pudo escribir el archivo en el disco.",
            UPLOAD_ERR_EXTENSION => "Una extensión de PHP detuvo la subida del archivo.",
        ];
        $errorCode = $_FILES['profile_image']['error'];
        $response['message'] = $uploadErrors[$errorCode] ?? 'Error desconocido durante la subida del archivo.';
    }
}

$conn->close();
echo json_encode($response);
?>