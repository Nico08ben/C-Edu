<?php
// (PHP para obtener datos de usuario - MODIFICADO para buscar nombre si no está en sesión)

// 1. Asegúrate de iniciar o reanudar la sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir la conexión a la base de datos
require_once __DIR__ . "/../conexion.php"; // Ajusta la ruta si es necesario

// 3. Obtener datos básicos de la sesión
$id_usuario = $_SESSION['id_usuario'] ?? null;
// Intentamos obtener el nombre de la sesión primero. Si no está, lo dejamos como null temporalmente.
$nombre_usuario = $_SESSION['nombre_usuario'] ?? null;

$user_data = ['nombre_rol' => 'Sin rol', 'materia' => null]; // Valores por defecto para rol y materia

// 4. Si el usuario está logueado (tenemos su ID)
if ($id_usuario) {

    // --- Lógica AÑADIDA para obtener el nombre si no está en la sesión ---
    if ($nombre_usuario === null) {
        // El nombre no está en la sesión, lo buscamos en la base de datos
        // Asumo que la tabla 'usuario' tiene una columna llamada 'nombre'.
        // ¡Ajusta 'nombre' al nombre real de tu columna si es diferente!
        $stmt_nombre = $conn->prepare("SELECT nombre_usuario FROM usuario WHERE id_usuario = ?");

        if ($stmt_nombre) {
            $stmt_nombre->bind_param("i", $id_usuario);
            $stmt_nombre->execute();
            $result_nombre = $stmt_nombre->get_result();
            $user_name_data = $result_nombre->fetch_assoc();

            if ($user_name_data && isset($user_name_data['nombre_usuario'])) {
                $nombre_usuario = $user_name_data['nombre_usuario'];
                // Opcional: Guardar el nombre en la sesión para futuras cargas de página
                // $_SESSION['nombre_usuario'] = $nombre_usuario; // Descomenta si quieres guardarlo
            } else {
                // El usuario ID existe, pero no se encontró el nombre en la DB (caso extraño)
                $nombre_usuario = 'Usuario Desconocido';
            }
            $stmt_nombre->close();
        } else {
            // Error al preparar la consulta del nombre
            $nombre_usuario = 'Error obteniendo nombre';
        }
    }
    // --- Fin de Lógica AÑADIDA ---


    // --- Lógica original para obtener Rol y Materia (sin cambios) ---
    $stmt_rol_materia = $conn->prepare("SELECT r.tipo_rol AS nombre_rol, m.nombre_materia AS materia
        FROM usuario u
        INNER JOIN rol r ON u.id_rol = r.id_rol
        LEFT JOIN materia m ON u.id_materia = m.id_materia
        WHERE u.id_usuario = ?");
    if ($stmt_rol_materia) {
        $stmt_rol_materia->bind_param("i", $id_usuario);
        $stmt_rol_materia->execute();
        $result_rol_materia = $stmt_rol_materia->get_result();
        $temp_data = $result_rol_materia->fetch_assoc();
        if ($temp_data) {
            $user_data = $temp_data;
        }
        $stmt_rol_materia->close();
    }
    // --- Fin de Lógica original ---

    // Considera cerrar la conexión más tarde si se necesita en otras partes de la página
    // $conn->close();
} else {
    // Si el usuario NO está logueado ($id_usuario es null)
    // Nos aseguramos de que el nombre_usuario sea 'Invitado'
    $nombre_usuario = 'Invitado';
    // user_data ya está en valores por defecto ('Sin rol')
}


// 5. Formatear el rol para mostrar
$rol_display = htmlspecialchars($user_data['nombre_rol']);
// Tu lógica original para maestros con materia
if (strtolower($user_data['nombre_rol']) === 'maestro' && !empty($user_data['materia'])) {
    // No añadimos la materia aquí según la imagen, solo el rol base
    $rol_display = "Maestro de " . htmlspecialchars($user_data['materia']);
}
?>

<div id="user-profile-box">

    <?php include "notificacion.php"; // Asegúrate de que esta ruta es correcta ?>

    <div class="profile-text">
        <span class="name"><?= htmlspecialchars($nombre_usuario) ?></span>
        <span class="role"><?= $rol_display ?></span>
    </div>

    <a href="../UserProfile/index.php" class="profile-button">
        Perfil
    </a>
</div>
</div>

<?php
// Opcional: Cerrar la conexión a la base de datos si se abrió aquí y no en conexion.php (o si no se cerró antes)
if (isset($conn) && $conn) {
    // $conn->close(); // Descomenta si necesitas cerrar la conexión aquí
}
?>

