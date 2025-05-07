<?php
// Ensure NO output (including spaces, BOM, or HTML) exists before this line
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session if not already active
}

require_once __DIR__ . '/../config/database.php';

$id_usuario = $_SESSION['id_usuario'] ?? null;
$fila = [];

if ($id_usuario) {
    $stmt = $conn->prepare("SELECT r.tipo_rol AS nombre_rol, m.nombre_materia AS materia 
        FROM usuario u 
        INNER JOIN rol r ON u.id_rol = r.id_rol 
        LEFT JOIN materia m ON u.id_materia = m.id_materia 
        WHERE u.id_usuario = ?");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $fila = $result->fetch_assoc();
    $stmt->close();
}
?>
<!-- HTML starts AFTER all PHP logic -->
<div id="user-profile-box" class="user-info">
    <div class="profile">
        <div class="profile-text">
            <span class="name"><?= htmlspecialchars($_SESSION['nombre_usuario'] ?? 'Invitado') ?></span>
            <span class="role">
                <?php
                $rol = htmlspecialchars($fila['nombre_rol'] ?? 'Sin rol asignado');
                if (strtolower($rol) === 'maestro' && !empty($fila['materia'])) {
                    echo "Maestro de " . htmlspecialchars($fila['materia']);
                } else {
                    echo $rol;
                }
                ?>
            </span>
        </div>
        <a href="user_profile.php">
            <img src="../../assets/perfil.jpg" alt="Perfil">
        </a>
    </div>
</div>