<?php
// api/crear_notificacion.php
// Esta función se debe incluir y llamar desde otros scripts PHP de tu backend
// donde se origina el evento que genera la notificación.

// Ejemplo de cómo podrías llamarla desde otro script:
/*
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once 'api/conexion.php'; // $conn estará disponible aquí
require_once 'api/crear_notificacion.php';

// Supongamos que se acaba de asignar una tarea al usuario con ID 5
$idUsuarioDestino = 5;
$tipo = 'nueva_tarea';
$mensaje = 'Se te ha asignado la tarea: "Completar informe semanal".';
$enlace = '/tareas/ver/123'; // Enlace a la tarea específica

if (crearNotificacion($conn, $idUsuarioDestino, $tipo, $mensaje, $enlace)) {
    // Notificación creada exitosamente
} else {
    // Error al crear notificación
}
$conn->close(); // Cierra la conexión si ya no la necesitas en el script que llama.
*/

function crearNotificacion(mysqli $conn, int $idUsuario, string $tipoNotificacion, string $mensaje, ?string $enlace = null): bool {
    $sql = "INSERT INTO notificacion (id_usuario, tipo_notificacion, mensaje, enlace, estado_notificacion)
            VALUES (?, ?, ?, ?, 'no leída')";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Error al preparar la consulta para crear notificación: " . $conn->error);
        return false;
    }

    // 'isss' para integer, string, string, string
    $stmt->bind_param('isss', $idUsuario, $tipoNotificacion, $mensaje, $enlace);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        error_log("Error al ejecutar la creación de notificación para usuario $idUsuario: " . $stmt->error);
        $stmt->close();
        return false;
    }
}
?>