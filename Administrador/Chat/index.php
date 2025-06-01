<?php
session_start();
$theme_class = '';
if (isset($_SESSION['rol'])) {
    if ($_SESSION['rol'] == 0) { // 0 para Admin
        $theme_class = 'theme-admin';
    } elseif ($_SESSION['rol'] == 1) { // 1 para Docente
        $theme_class = 'theme-docente';
    }
}
include '../../conexion.php'; 

// --- Logic for LOGGED_IN_USER_FOTO_URL ---
$webRootForIndex = '/C-edu/'; // Define web root path
$loggedInUserFotoUrl = '';

// Check if 'foto_perfil_url' is set in session and is not empty
// This session variable should hold the relative path like 'uploads/profile_pictures/avatar.jpg'
if (isset($_SESSION['foto_perfil_url']) && !empty($_SESSION['foto_perfil_url'])) {
    $loggedInUserFotoUrl = $webRootForIndex . htmlspecialchars($_SESSION['foto_perfil_url'], ENT_QUOTES, 'UTF-8');
} else {
    // Default avatar path relative to web root
    $loggedInUserFotoUrl = $webRootForIndex . '/C-edu/uploads/profile_pictures/default-avatar.png';
}
// --- End Logic for LOGGED_IN_USER_FOTO_URL ---
?>
<!DOCTYPE html>
<html lang="es" class="<?php echo $theme_class; ?>">
<head>
    <?php include "../../SIDEBAR/Admin/head.php" ?>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.2.0/fonts/remixicon.css" rel="stylesheet">
    <title>Chat</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="upload.php">
</head>

<body>
    <?php include "../../SIDEBAR/Admin/sidebar.php" ?>

    <section class="home">
    <section class="chat-section">
        <div class="chat-container">
            <div class="chat-content">
                <div class="content-sidebar">
                    <div class="content-sidebar-title">Chats</div>
                    <form action="" class="content-sidebar-form">
                        <input type="search" class="content-sidebar-input" placeholder="Search...">
                        <button type="submit" class="content-sidebar-submit"><i class="ri-search-line"></i></button>
                    </form>
                    <div class="content-messages">
                        <ul class="content-messages-list">
                            <li class="content-message-title"><span>Recently</span></li>
                            </ul>
                    </div>
                </div>
                <div class="conversation conversation-default active">
                    <i class="ri-chat-3-line"></i>
                    <p>Select chat and view conversation!</p>
                </div>

                <div class="conversation" id="conversation-active-chat" style="display: none;"> <div class="conversation-top">
                        <button type="button" class="conversation-back"><i class="ri-arrow-left-line"></i></button>
                        <div class="conversation-user">
                            <img class="conversation-user-image" src="" alt=""> <div>
                                <div class="conversation-user-name"></div> <div class="conversation-user-status online">online</div> </div>
                        </div>
                        <div class="conversation-buttons">
                            <button type="button"><i class="ri-phone-fill"></i></button>
                            <button type="button"><i class="ri-vidicon-line"></i></button>
                            <button type="button"><i class="ri-information-line"></i></button>
                        </div>
                    </div>
                    <div class="conversation-main">
                        <ul class="conversation-wrapper">
                            </ul>
                    </div>
                    <div class="conversation-reply-preview" style="display: none;">
                        <div class="reply-text"></div>
                        <button type="button" class="cancel-reply"><i class="ri-close-line"></i></button>
                    </div>
                    <div class="conversation-form">
                        <input type="file" id="imageInput" accept="image/*" style="display: none;">
                        <button type="button" class="conversation-form-file emoji-btn"><i class="ri-emotion-line"></i></button>
                        <button type="button" class="conversation-form-sticker"><i class="ri-emoji-sticker-line"></i></button>
                        <button type="button" class="conversation-form-file" onclick="document.getElementById('imageInput').click()"><i class="ri-image-line"></i></button>
                        <div class="conversation-form-group">
                            <textarea class="conversation-form-input" rows="1" placeholder="Type here..."></textarea>
                            <div id="sound-visualizer-container"></div>
                            <button type="button" class="conversation-form-record"><i class="ri-mic-line"></i></button>
                        </div>
                        <button type="button" class="conversation-form-button conversation-form-submit"><i class="ri-send-plane-2-line"></i></button>
                    </div>
                </div>
                </div>
            </div>
    </section>
    <script>
        const LOGGED_IN_USER_ID = <?php echo isset($_SESSION['id_usuario']) ? intval($_SESSION['id_usuario']) : 'null'; ?>;
        // LOGGED_IN_USER_FOTO_URL is now a full, correctly constructed URL
        const LOGGED_IN_USER_FOTO_URL = '<?php echo $loggedInUserFotoUrl; ?>';
        // These might be less needed if the server always provides full URLs for photos
        const PROFILE_PIC_BASE_PATH_DEPRECATED = '<?php echo $webRootForIndex . "uploads/"; ?>'; // Example, likely not used by new JS
        const DEFAULT_AVATAR_PATH = '<?php echo $webRootForIndex . "Administrador/Chat/assets/images/default-avatar.png"; ?>';
    </script>
    <script src="script.js"></script> <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@^1/index.js"></script>
    <emoji-picker style="position: absolute; bottom: 60px; right: 80px; display: none;"></emoji-picker>
    <div id="imageModal" class="image-modal" style="display:none;">
        <span class="image-modal-close" style="position: absolute; top: 20px; right: 30px; font-size: 40px; color: white; cursor: pointer;">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>
    <div class="sticker-panel" id="stickerPanel"></div>
</body>
</html>