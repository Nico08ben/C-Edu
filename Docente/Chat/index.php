<!DOCTYPE html>
<html lang="es">

<?php
session_start();
// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php';

// Obtener todos los usuarios registrados, excluyendo al actual
$stmt = $pdo->prepare("SELECT id_usuario, nombre_usuario, email_usuario FROM usuario WHERE id_usuario != ?");
$stmt->execute([$_SESSION['id_usuario']]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Chat Institucional</title>
  <link rel="stylesheet" href="chat.css">
</head>
<body>
  <div class="container">
    <div class="sidebar-chat">
      <input type="text" class="search-bar" placeholder="Buscar contactos...">
      <div class="contact-list">
        <?php foreach ($usuarios as $usuario): ?>
          <div class="contact" 
               data-id="<?= $usuario['id_usuario'] ?>" 
               data-nombre="<?= htmlspecialchars($usuario['nombre_usuario']) ?>" 
               data-email="<?= htmlspecialchars($usuario['email_usuario']) ?>">
            <img src="default.png" alt="Avatar">
            <h3><?= htmlspecialchars($usuario['nombre_usuario']) ?></h3>
            <span class="status">Conectado</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="chat-window">
      <div class="chat-header">
        <img src="default.png" alt="Avatar">
        <h3>Selecciona un contacto</h3>
        <span class="status">Desconectado</span>
      </div>
      <div class="chat-body">
        <p>Inicia un chat seleccionando un contacto</p>
      </div>
      <div class="chat-footer">
        <input type="text" placeholder="Escribe un mensaje...">
        <button>Enviar</button>
      </div>
    </div>
  </div>
  <script src="script.js"></script>
</body>
</html>
