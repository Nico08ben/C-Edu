<?php
// (PHP para obtener datos de usuario - sin cambios)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/../conexion.php"; // Ajusta la ruta si es necesario

$id_usuario = $_SESSION['id_usuario'] ?? null;
$nombre_usuario = $_SESSION['nombre_usuario'] ?? 'Invitado';
$user_data = ['nombre_rol' => 'Sin rol', 'materia' => null]; // Valores por defecto

if ($id_usuario) {
    $stmt = $conn->prepare("SELECT r.tipo_rol AS nombre_rol, m.nombre_materia AS materia
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        LEFT JOIN materia m ON u.id_materia = m.id_materia
        WHERE u.id_usuario = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $temp_data = $result->fetch_assoc();
        if ($temp_data) {
            $user_data = $temp_data;
        }
        $stmt->close();
    }
    // $conn->close(); // Considera cerrar la conexión más tarde si se necesita
}

$rol_display = htmlspecialchars($user_data['nombre_rol']);
if (strtolower($user_data['nombre_rol']) === 'maestro' && !empty($user_data['materia'])) {
    // No añadimos la materia aquí según la imagen, solo el rol base
    // $rol_display = "Maestro de " . htmlspecialchars($user_data['materia']);
}
?>

<div id="user-profile-box">

    <?php include "notificacion.php"; ?>

    <div class="profile-text">
        <span class="name"><?= htmlspecialchars($nombre_usuario) ?></span>
        <span class="role"><?= $rol_display ?></span>
    </div>

    <a href="../UserProfile/index.php" class="profile-button">
        Perfil
    </a>
    </div>
    </div>