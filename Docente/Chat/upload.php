<?php
// Establecer la cabecera para la respuesta JSON
header('Content-Type: application/json');

// Definir la respuesta base
$response = ['success' => false, 'message' => '', 'imageUrl' => null];

// 1. Verificar el m�todo de la solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'M�todo no permitido. Solo se aceptan solicitudes POST.';
    echo json_encode($response);
    exit;
}

// 2. Verificar si el archivo fue subido y no hay errores iniciales
if (!isset($_FILES['image']) || !is_uploaded_file($_FILES['image']['tmp_name'])) {
    $response['message'] = 'No se ha subido ning�n archivo o el archivo no es v�lido.';
    echo json_encode($response);
    exit;
}

// 3. Manejar errores de subida de PHP
$fileError = $_FILES['image']['error'];
if ($fileError !== UPLOAD_ERR_OK) {
    $phpUploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'El archivo excede la directiva upload_max_filesize en php.ini.',
        UPLOAD_ERR_FORM_SIZE  => 'El archivo excede la directiva MAX_FILE_SIZE especificada en el formulario HTML.',
        UPLOAD_ERR_PARTIAL    => 'El archivo se subi� solo parcialmente.',
        UPLOAD_ERR_NO_FILE    => 'No se subi� ning�n archivo.',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta una carpeta temporal.',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco.',
        UPLOAD_ERR_EXTENSION  => 'Una extensi�n de PHP detuvo la subida del archivo.',
    ];
    $response['message'] = $phpUploadErrors[$fileError] ?? 'Error desconocido al subir el archivo.';
    echo json_encode($response);
    exit;
}

// 4. Definir par�metros y validaciones
$uploadDir = 'uploads/';
$allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
$maxFileSize = 5 * 1024 * 1024; // 5 MB

// 5. Validar tama�o del archivo
if ($_FILES['image']['size'] > $maxFileSize) {
    $response['message'] = 'El archivo es demasiado grande. El tama�o m�ximo permitido es de ' . ($maxFileSize / 1024 / 1024) . ' MB.';
    echo json_encode($response);
    exit;
}

// 6. Validar tipo MIME y extensi�n del archivo
$fileInfo = finfo_open(FILEINFO_MIME_TYPE);
$detectedMimeType = finfo_file($fileInfo, $_FILES['image']['tmp_name']);
finfo_close($fileInfo);

$originalFileName = basename($_FILES['image']['name']); // Para obtener el nombre de forma segura
$fileExtension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));

if (!in_array($detectedMimeType, $allowedMimeTypes) || !in_array($fileExtension, $allowedExtensions)) {
    $response['message'] = 'Tipo de archivo no permitido. Solo se aceptan im�genes JPG, JPEG, PNG, GIF o WEBP.';
    echo json_encode($response);
    exit;
}

// 7. Crear un nombre de archivo �nico y seguro
// Usar $fileExtension (obtenida de forma segura) en lugar de extraerla de nuevo
$safeFileName = preg_replace("/[^A-Za-z0-9_.-]/", "_", $originalFileName); // Sanitizar nombre original
$newName = uniqid('img_', true) . '.' . $fileExtension; // uniqid con m�s entrop�a
$uploadPath = $uploadDir . $newName;

// 8. Verificar y crear el directorio de subida si no existe
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) { // 0755 es un permiso com�n para directorios
        $response['message'] = 'Error al crear el directorio de subida.';
        error_log('Fallo al crear el directorio: ' . $uploadDir); // Registrar error en el servidor
        echo json_encode($response);
        exit;
    }
} elseif (!is_dir($uploadDir) || !is_writable($uploadDir)) {
    $response['message'] = 'El directorio de subida no es v�lido o no tiene permisos de escritura.';
    error_log('Directorio de subida no v�lido o sin permisos: ' . $uploadDir);
    echo json_encode($response);
    exit;
}

// 9. Mover el archivo subido
if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
    $response['success'] = true;
    $response['message'] = 'Imagen subida correctamente.';
    $response['imageUrl'] = $uploadPath; // Devolver la ruta relativa
} else {
    $response['message'] = 'Error al mover el archivo subido.';
    // Podr�as a�adir un registro de error aqu� tambi�n si es necesario
    // error_log('Error al mover archivo subido: de ' . $_FILES['image']['tmp_name'] . ' a ' . $uploadPath);
}

echo json_encode($response);
?>