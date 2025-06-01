// script.js

// -----------------------------------------------------------------------------
// #region GLOBAL STATE & CONSTANTS
// -----------------------------------------------------------------------------
let activeChatUserId = null;
let activeChatUserName = null;
let activeChatUserFoto = null;
let lastMessageIdChecked = 0;
let pollingInterval = null;

// Constants from index.php (should be globally available)
// const LOGGED_IN_USER_ID, LOGGED_IN_USER_FOTO_URL, DEFAULT_AVATAR_PATH are expected to be defined in the HTML <script> tag

// Variables from original script (to be integrated or used as reference)
let activeReplyPreview = null;
let replyingToElement = null;
let mediaRecorder;
let audioChunks = [];
let isRecording = false;
let visualizerAudioContext;
let visualizerAnalyser;
let visualizerSourceNode;
let visualizerDataArray;
let visualizerRequestFrameId;
let originalStreamForRecorder;

const STICKERS_LIST = [
    "https://media.giphy.com/media/xT9IgG50Fb7Mi0prBC/giphy.gif",
    "https://media.giphy.com/media/3o7abB06u9bNzA8lu8/giphy.gif",
    "https://media.giphy.com/media/l0Exk8EUzSLsrErEQ/giphy.gif",
    "https://media.giphy.com/media/KziKCpvrGngHbYjaUF/giphy.gif",
    "https://media.giphy.com/media/YTbZzCkRQCEJa/giphy.gif",
    "https://media0.giphy.com/media/v1.Y2lkPTc5MGI3NjExbHM1ZzRrNDZjMzJyMjJqdTVwY3A3NXVqamVhMWVleXVrOGxzaDI2biZlcD12MV9pbnRlcm5hbF9naWZfYnlfaWQmY3Q9Zw/1ViLp0GBYhTcA/giphy.gif"
];
// #endregion

// -----------------------------------------------------------------------------
// #region HELPER FUNCTIONS (some from original script.js)
// -----------------------------------------------------------------------------
function getCurrentTimeJS() { // Renamed to avoid conflict if PHP has one
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function escapeHTML(str) {
    if (str === null || str === undefined) return '';
    const div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

function scrollToBottom(element) {
    if (element) {
        element.scrollTop = element.scrollHeight;
    }
}

function clearActiveReplyPreviewGlobal() { // Renamed
    const replyPreviewEl = document.querySelector('.conversation-reply-preview');
    if (replyPreviewEl) {
        replyPreviewEl.style.display = 'none';
        const replyTextEl = replyPreviewEl.querySelector('.reply-text');
        if (replyTextEl) replyTextEl.innerHTML = '';
    }
    activeReplyPreview = null; // From original script
    replyingToElement = null; // From original script
}

// Sound visualizer functions (ensure these are complete implementations from your original script)
function initializeVisualizerNodes(streamForVisualizer) {
    // This is a placeholder. Ensure you have the full implementation from your original script.js.
    console.log("Vis Init placeholder. Original stream for visualizer:", streamForVisualizer);
    if (visualizerAudioContext && visualizerSourceNode) {
        visualizerSourceNode.disconnect();
    }
    if (!visualizerAudioContext) visualizerAudioContext = new AudioContext();

    try {
        visualizerSourceNode = visualizerAudioContext.createMediaStreamSource(streamForVisualizer);
        visualizerAnalyser = visualizerAudioContext.createAnalyser();
        visualizerAnalyser.fftSize = 256; // Example value
        const bufferLength = visualizerAnalyser.frequencyBinCount;
        visualizerDataArray = new Uint8Array(bufferLength);
        visualizerSourceNode.connect(visualizerAnalyser);

        const visualizerContainer = document.getElementById('sound-visualizer-container');
        if (visualizerContainer) {
            visualizerContainer.innerHTML = ''; // Clear previous bars
            const numberOfBars = 12; // Example value
            for (let i = 0; i < numberOfBars; i++) {
                const bar = document.createElement('div');
                // Add styling for bars if not in CSS, e.g., bar.style.width, bar.style.margin, bar.style.backgroundColor
                visualizerContainer.appendChild(bar);
            }
        }
    } catch (e) {
        console.error("Error initializing visualizer nodes:", e);
    }
}
function startSoundVisualizer(streamToVisualize) {
    // This is a placeholder. Ensure you have the full implementation from your original script.js.
    console.log("Vis Start placeholder. Stream to visualize:", streamToVisualize);
    isRecording = true; /* ensure isRecording is set */
    if (visualizerAudioContext && visualizerAudioContext.state === 'suspended') {
        visualizerAudioContext.resume();
    }
    initializeVisualizerNodes(streamToVisualize); // Initialize with the correct stream
    if (visualizerAnalyser) { // Only draw if analyser is ready
        drawSoundVisualizer();
    }
}
function drawSoundVisualizer() {
    // This is a placeholder. Ensure you have the full implementation from your original script.js.
    if (!isRecording || !visualizerAnalyser || !visualizerDataArray) {
        if (visualizerRequestFrameId) cancelAnimationFrame(visualizerRequestFrameId);
        visualizerRequestFrameId = null;
        return;
    }
    visualizerRequestFrameId = requestAnimationFrame(drawSoundVisualizer);
    visualizerAnalyser.getByteFrequencyData(visualizerDataArray);
    // Add logic to update bar heights based on visualizerDataArray
    const visualizerContainer = document.getElementById('sound-visualizer-container');
    if (visualizerContainer) {
        const bars = visualizerContainer.children;
        const containerHeight = 24; // Max height for bars
        for (let i = 0; i < bars.length; i++) {
            if (bars[i]) {
                const barHeight = (visualizerDataArray[i] / 255) * containerHeight;
                bars[i].style.height = `${Math.max(1, barHeight)}px`; // Ensure min height of 1px
            }
        }
    }
}
function stopSoundVisualizer() {
    // This is a placeholder. Ensure you have the full implementation from your original script.js.
    console.log("Vis Stop placeholder");
    isRecording = false; /* ensure isRecording is reset */
    if (visualizerRequestFrameId) {
        cancelAnimationFrame(visualizerRequestFrameId);
        visualizerRequestFrameId = null;
    }
    if (visualizerSourceNode) {
        visualizerSourceNode.disconnect();
        visualizerSourceNode = null;
    }
    // visualizerAnalyser = null; // Don't nullify analyser here, it's part of context
    // visualizerDataArray = null;
    const visualizerContainer = document.getElementById('sound-visualizer-container');
    if (visualizerContainer) {
        visualizerContainer.innerHTML = ''; // Clear bars
    }
    if (originalStreamForRecorder && typeof originalStreamForRecorder.getTracks === 'function') {
        originalStreamForRecorder.getTracks().forEach(track => track.stop());
        console.log("Microphone stream tracks stopped in stopSoundVisualizer.");
    }
}

// #endregion

// -----------------------------------------------------------------------------
// #region CORE CHAT LOGIC
// -----------------------------------------------------------------------------

function initChatApp() {
    // ---- NUEVO CÓDIGO PARA LEER PARÁMETROS URL ----
    const urlParams = new URLSearchParams(window.location.search);
    const userIdFromUrl = urlParams.get('userId');
    const userNameFromUrl = urlParams.get('userName');
    const userFotoFromUrl = urlParams.get('userFoto');

    let chatOpenedFromUrl = false;
    if (userIdFromUrl && userNameFromUrl && userFotoFromUrl) {
        console.log(`Attempting to open chat from URL: UserID=${userIdFromUrl}, Name=${userNameFromUrl}`);
        // Llama a openConversationWithUser con los datos de la URL
        // Asegúrate de que la UI principal del chat se muestre si está oculta
        const conversationDiv = document.getElementById('conversation-active-chat');
        const defaultConversationDiv = document.querySelector('.conversation-default');

        if (conversationDiv && defaultConversationDiv) {
            defaultConversationDiv.classList.remove('active');
            defaultConversationDiv.style.display = 'none';
            conversationDiv.classList.add('active');
            // Asegúrate que el display sea el correcto (ej. 'flex' si es un contenedor flex)
            // openConversationWithUser también maneja parte de esto.
            conversationDiv.style.display = ''; // O 'flex'
        }

        openConversationWithUser(userIdFromUrl, userNameFromUrl, userFotoFromUrl);
        chatOpenedFromUrl = true;
    }
    // ---- FIN DEL NUEVO CÓDIGO ----

    // El resto de la inicialización para la página de chat
    loadUserList(); // Esto carga la lista de usuarios en la barra lateral de la página de chat
    setupMessageSendingAndInput();
    setupStaticEventListeners();
    console.log("Chat App Initialized. Current User ID:", LOGGED_IN_USER_ID, "Chat opened from URL:", chatOpenedFromUrl);

    // Opcional: Si no se abrió un chat desde la URL, asegúrate de que la vista por defecto esté visible
    if (!chatOpenedFromUrl) {
        const defaultConversationDiv = document.querySelector('.conversation-default');
        const activeConversationDiv = document.getElementById('conversation-active-chat');
        if (defaultConversationDiv && !defaultConversationDiv.classList.contains('active')) {
            defaultConversationDiv.classList.add('active');
            defaultConversationDiv.style.display = ''; // o 'flex'
        }
        if (activeConversationDiv && activeConversationDiv.classList.contains('active')) {
            activeConversationDiv.classList.remove('active');
            activeConversationDiv.style.display = 'none';
        }
    }
}

// En script.js, dentro de la función loadUserList:

function loadUserList() {
    const userListElement = document.querySelector('.content-messages-list');
    if (!userListElement) {
        console.error("User list container not found.");
        return;
    }
    // Limpiar solo los elementos <li> que no sean títulos
    userListElement.querySelectorAll('li:not(.content-message-title)').forEach(li => li.remove());

    fetch('get_users.php') // Asumiendo que está en la misma carpeta que index.php del chat
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success && data.users) {
                data.users.forEach(user => {
                    const userLi = document.createElement('li');
                    // user.foto_perfil_url_url ya es la URL completa o el avatar por defecto
                    const userPhoto = user.foto_perfil_url_url; 

                    // Preparar texto del último mensaje y hora
                    let lastMessageText = 'Conversación no iniciada';
                    if (user.lastMessageContent) {
                        const maxLen = 30;
                        if (user.lastMessageContent.toLowerCase().startsWith('uploads/') || /\.(jpeg|jpg|gif|png|webp)$/i.test(user.lastMessageContent.toLowerCase())) {
                            lastMessageText = '[Imagen]';
                        } else if (user.lastMessageContent.toLowerCase().startsWith('http://') || user.lastMessageContent.toLowerCase().startsWith('https://')) {
                            lastMessageText = '[Sticker]';
                        } else if (user.lastMessageContent.toLowerCase().startsWith('blob:http')) {
                            lastMessageText = '[Mensaje de voz]';
                        } else {
                            lastMessageText = user.lastMessageContent.length > maxLen ? user.lastMessageContent.substring(0, maxLen) + '...' : user.lastMessageContent;
                        }
                        lastMessageText = escapeHTML(lastMessageText);
                    }
                    let lastMessageTime = '';
                    if (user.lastMessageDate) {
                        try {
                            const dateStr = user.lastMessageDate.replace(' ', 'T');
                            const dateObj = new Date(dateStr); // Considera manejo de zona horaria si es necesario
                            const today = new Date();
                            if (dateObj.toDateString() === today.toDateString()) {
                                lastMessageTime = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            } else {
                                lastMessageTime = dateObj.toLocaleDateString([], { day: '2-digit', month: 'short' });
                            }
                        } catch (e) { console.error("Error formatting date:", user.lastMessageDate, e); }
                    }
                    // ----- Lógica para el indicador de no leídos -----
                    let unreadCount = parseInt(user.unreadCount) || 0;
                    let unreadIndicatorHTML = '';

                    if (unreadCount > 0) {
                        // Si hay mensajes no leídos, muestra el contador (estilo de insignia/badge)
                        unreadIndicatorHTML = `<span class="content-message-unread">${unreadCount}</span>`;
                    } else {
                        // Si no hay mensajes no leídos, muestra la barra verde (estilo de barra indicadora)
                        // Se usará una clase CSS adicional para diferenciar el estilo
                        unreadIndicatorHTML = `<span class="content-message-unread is-indicator-bar"></span>`;
                    }

                    // ---- Construcción del HTML del elemento de lista ----
                    userLi.innerHTML = `
                        <a href="#" data-user-id="${user.id_usuario}"
                                     data-user-name="${escapeHTML(user.fullName)}"
                                     data-user-foto="${escapeHTML(user.foto_perfil_url_url)}">
                            <img class="content-message-image" src="${escapeHTML(user.foto_perfil_url_url)}" alt="${escapeHTML(user.fullName)}">
                            <span class="content-message-info">
                                <span class="content-message-name">${escapeHTML(user.fullName)}</span>
                                <span class="content-message-text">${lastMessageText}</span>
                            </span>
                            <span class="content-message-more">
                                ${unreadIndicatorHTML}
                                <span class="content-message-time">${lastMessageTime}</span>
                            </span>
                        </a>`;
                    userListElement.appendChild(userLi);

                    userLi.querySelector('a').addEventListener('click', (e) => {
                        e.preventDefault();
                        const targetUser = e.currentTarget;
                        openConversationWithUser(
                            targetUser.dataset.userId,
                            targetUser.dataset.userName,
                            targetUser.dataset.userFoto
                        );
                    });
                });
            } else {
                console.error('Failed to load users:', data.message || 'No users data');
                const errorLi = document.createElement('li');
                errorLi.textContent = 'No se pudieron cargar los usuarios.';
                errorLi.style.textAlign = 'center';
                errorLi.style.padding = '10px';
                userListElement.appendChild(errorLi);
            }
        })
        .catch(error => {
            console.error('Error fetching users:', error);
            if (userListElement) {
                 const errorLi = document.createElement('li');
                 errorLi.textContent = `Error al cargar usuarios: ${error.message}`;
                 errorLi.style.textAlign = 'center';
                 errorLi.style.padding = '10px';
                 userListElement.appendChild(errorLi);
            }
        });
}

function openConversationWithUser(userId, userName, userFoto) {
    console.log(`Opening conversation with User ID: ${userId}, Name: ${userName}`);
    activeChatUserId = parseInt(userId);
    activeChatUserName = userName;
    activeChatUserFoto = userFoto;
    lastMessageIdChecked = 0; // Reset for new conversation

    const conversationDiv = document.getElementById('conversation-active-chat');
    const defaultConversationDiv = document.querySelector('.conversation-default');
    const conversationWrapper = conversationDiv.querySelector('.conversation-wrapper');
    const conversationTopName = conversationDiv.querySelector('.conversation-user-name');
    const conversationTopImage = conversationDiv.querySelector('.conversation-user-image');

    if (!conversationDiv || !defaultConversationDiv || !conversationWrapper || !conversationTopName || !conversationTopImage) {
        console.error("One or more conversation UI elements are missing.");
        return;
    }

    // Update header
    conversationTopName.textContent = userName;
    conversationTopImage.src = userFoto;
    conversationTopImage.alt = userName;

    // Show the target conversation, hide default
    defaultConversationDiv.classList.remove('active');
    defaultConversationDiv.style.display = 'none';
    conversationDiv.classList.add('active');
    conversationDiv.style.display = ''; // Or 'flex' if it's a flex container

    // Clear previous messages
    conversationWrapper.innerHTML = `<div class="coversation-divider"><span>${new Date().toLocaleDateString()}</span></div>`;

    fetchAndDisplayMessages();

    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(fetchAndDisplayMessages, 5000); // Poll every 5 seconds
}

function fetchAndDisplayMessages() {
    if (!activeChatUserId) return;

    fetch(`get_messages.php?user_id=${activeChatUserId}&last_message_id=${lastMessageIdChecked}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success && data.messages) {
                const conversationWrapper = document.querySelector('#conversation-active-chat .conversation-wrapper');
                if (!conversationWrapper) return;

                let newMessagesFetched = false;
                data.messages.forEach(msg => {
                    if (!document.getElementById(`msg-${msg.id_mensaje}`)) {
                        renderMessageToDOM(msg, conversationWrapper);
                        newMessagesFetched = true;
                    }
                    lastMessageIdChecked = Math.max(lastMessageIdChecked, msg.id_mensaje);
                });

                if (newMessagesFetched) {
                    scrollToBottom(conversationWrapper.parentElement);
                }
            } else if (data.success && data.messages.length === 0) {
                // No new messages
            } else {
                console.error('Failed to fetch messages:', data.message || 'No messages data');
            }
        })
        .catch(error => {
            console.error('Error fetching messages:', error); // This will catch network errors and json parse errors
        });
}

// Inside script.js

function renderMessageToDOM(msg, container) {
    // isMe is true if the message is from the currently logged-in user.
    const isMe = msg.id_emisor === LOGGED_IN_USER_ID;
    const li = document.createElement('li');
    li.classList.add('conversation-item');
    li.id = `msg-${msg.id_mensaje}`;

    // Apply 'me' class if the message is NOT from the current user (i.e., it's a received message)
    if (!isMe) { // <<<<------ THE ONLY CHANGE IS HERE: `!isMe` instead of `isMe`
        li.classList.add('me');
    }

    // Profile photo for the sender of this message
    const senderMessagePhoto = msg.emisor_foto_url; 
    let messageContentHTML = '';
    const content = msg.contenido_mensaje;

    const chatContentImageBasePath = '/C-edu/Administrador/Chat/';

    if (content && (content.toLowerCase().endsWith('.jpg') || content.toLowerCase().endsWith('.jpeg') || content.toLowerCase().endsWith('.png') || content.toLowerCase().endsWith('.gif') || content.toLowerCase().endsWith('.webp'))) {
        if (content.startsWith('http://') || content.startsWith('https://')) {
            messageContentHTML = `<img class="message-image sticker-image" src="${escapeHTML(content)}" alt="Sticker" style="max-width: 180px; height: auto; border-radius: 8px;" />`;
        } else if (content.startsWith('uploads/')) {
            messageContentHTML = `<img class="message-image" src="${escapeHTML(chatContentImageBasePath + content)}" alt="Image" style="max-width: 300px; border-radius: 8px; height: auto;" />`;
        } else {
            messageContentHTML = `<img class="message-image" src="${escapeHTML(chatContentImageBasePath + 'uploads/' + content)}" alt="Image" style="max-width: 300px; border-radius: 8px; height: auto;" />`;
        }
    } else if (content && content.startsWith('blob:http')) {
        messageContentHTML = `
            <div class="voice-message" data-audio-src="${escapeHTML(content)}" style="background: white; padding: 10px 14px; border-radius: 20px; display: flex; align-items: center; gap: 10px; width: fit-content;">
                <button class="play-button" style="border: none; background: #e7bb41; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    <i class="ri-play-fill"></i>
                </button>
                <div class="voice-bar-wrapper" style="background: #d3f4ff; height: 4px; width: 120px; border-radius: 2px; position: relative;">
                    <div class="voice-bar" style="background: #00C2FF; height: 100%; width: 0%; transition: width 0.2s;"></div>
                </div>
                <div class="voice-time" style="font-size: 13px;">00:00</div>
            </div>`;
    } else if (content) {
        messageContentHTML = `<p>${escapeHTML(content)}</p>`;
    } else {
        messageContentHTML = `<p><em>Mensaje vacío o archivo no compatible.</em></p>`;
    }

    let time = '??:??';
    if (msg.fecha_envio) {
        try {
            const dateStr = msg.fecha_envio.replace(' ', 'T') + (msg.fecha_envio.includes('Z') ? '' : 'Z');
            const dateObj = new Date(dateStr);
            if (!isNaN(dateObj)) {
                time = dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            } else {
                console.warn("Invalid date format for message:", msg.id_mensaje, msg.fecha_envio);
            }
        } catch (e) {
            console.error("Error parsing date:", msg.fecha_envio, e);
        }
    }

    const dropdownHTML = `
        <div class="conversation-item-dropdown">
            <button type="button" class="conversation-item-dropdown-toggle"><i class="ri-more-2-line"></i></button>
            <ul class="conversation-item-dropdown-list">
                <li><a href="#" class="edit-btn" data-message-id="${msg.id_mensaje}"><i class="ri-pencil-fill"></i>Edit</a></li>
                <li><a href="#" class="forward-btn" data-message-id="${msg.id_mensaje}"><i class="ri-share-forward-line"></i>Forward</a></li>
                <li><a href="#" class="Delete-btn" data-message-id="${msg.id_mensaje}"><i class="ri-delete-bin-line"></i>Delete</a></li>
            </ul>
        </div>`;

    let finalMessageHTML = '';
    // The 'isMe' here determines if the dropdown appears (typically for messages the user sent).
    // If you want dropdowns on received messages (which now have '.me' class), you'd use !isMe here too.
    // For typical UI, dropdowns are for messages the user owns.
    // If '.me' class is now for received messages, you might want the dropdown for messages that *don't* have '.me' (i.e., where isMe is true).
    const showDropdown = isMe; // Standard: Dropdown for messages sent by the current user. Adjust if needed.

    if (content && content.startsWith('blob:http')) { // Audio message
        finalMessageHTML = `
            <div class="conversation-item-side">
                <img class="conversation-item-image" src="${escapeHTML(senderMessagePhoto)}" alt="User Avatar">
            </div>
            <div class="conversation-item-content">
                <div class="conversation-item-wrapper">
                    <div class="conversation-item-box" style="background: transparent; box-shadow: none; padding: 0;">
                        ${messageContentHTML} <div class="conversation-item-time" style="margin-top: 4px; ${!isMe ? '' : 'text-align: left;'}">${time}</div> 
                        ${showDropdown ? dropdownHTML : ''}
                    </div>
                </div>
            </div>`;
        // Note: ${!isMe ? '' : 'text-align: left;'} -> if it's a received message (now .me), align time normally (right), else align left.
        // You'll need to ensure your CSS for .me aligns content as you expect for received messages.
    } else { // Text or uploaded image message
        finalMessageHTML = `
            <div class="conversation-item-side">
                <img class="conversation-item-image" src="${escapeHTML(senderMessagePhoto)}" alt="User Avatar">
            </div>
            <div class="conversation-item-content">
                <div class="conversation-item-wrapper">
                    <div class="conversation-item-box">
                        <div class="conversation-item-text">
                            ${messageContentHTML} <div class="conversation-item-time">${time}</div>
                        </div>
                        ${showDropdown ? dropdownHTML : ''}
                    </div>
                </div>
            </div>`;
    }
    li.innerHTML = finalMessageHTML;
    container.appendChild(li);
}

// This function might no longer be needed if all paths are absolute or correctly relative from their source.
// function webRootPathForJS() {
//     return '/C-edu/';
// }

function sendMessageToServer(messageContent, isFilePlaceholder = false, type = 'text') {
    if ((!messageContent || messageContent.trim() === '') && !isFilePlaceholder) {
        console.log("Message content is empty and not a file placeholder.");
        return;
    }
    if (!activeChatUserId) {
        alert('Please select a chat to send a message.');
        return;
    }

    const payload = {
        receiver_id: activeChatUserId,
        message: messageContent
    };
    if (isFilePlaceholder) {
        payload.is_file_placeholder = true;
    }

    fetch('send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success && data.message_data) {
                const conversationWrapper = document.querySelector('#conversation-active-chat .conversation-wrapper');
                if (conversationWrapper) {
                    renderMessageToDOM(data.message_data, conversationWrapper);
                    scrollToBottom(conversationWrapper.parentElement);
                }
                if (type === 'text' && document.querySelector('.conversation-form-input')) {
                    document.querySelector('.conversation-form-input').value = '';
                    document.querySelector('.conversation-form-input').rows = 1;
                }
                clearActiveReplyPreviewGlobal();
            } else {
                alert('Error sending message: ' + (data.message || 'Unknown error from server'));
                console.error('Send message error (data):', data);
            }
        })
        .catch(error => {
            alert('Network error sending message. Check console.');
            console.error('Network send message error (fetch):', error);
        });
}

function setupMessageSendingAndInput() {
    const conversationFormSubmit = document.querySelector('.conversation-form-submit');
    const conversationFormInput = document.querySelector('.conversation-form-input');
    const imageUploadInput = document.getElementById('imageInput');

    if (conversationFormSubmit && conversationFormInput) {
        conversationFormSubmit.addEventListener('click', () => {
            sendMessageToServer(conversationFormInput.value.trim(), false, 'text');
        });

        conversationFormInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessageToServer(conversationFormInput.value.trim(), false, 'text');
            }
        });
        conversationFormInput.addEventListener('input', function () {
            this.rows = Math.max(1, this.value.split('\n').length);
        });
    }

    if (imageUploadInput) {
        imageUploadInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) { this.value = ""; return; }
            if (!file.type.startsWith('image/')) {
                alert('Solo se permiten imágenes.'); this.value = ""; return;
            }
            if (!activeChatUserId) {
                alert('Please select a chat before sending an image.'); this.value = ""; return;
            }

            const formData = new FormData();
            formData.append('image', file);

            fetch('upload.php', { method: 'POST', body: formData })
                .then(res => {
                    if (!res.ok) return res.text().then(text => { throw new Error(`Upload error: ${res.status} ${text}`); });
                    return res.json();
                })
                .then(data => {
                    if (data.success && data.imageUrl) {
                        // data.imageUrl is like "uploads/img_uniqueid.jpg"
                        // This path is relative to where upload.php saved it (Administrador/Chat/uploads/)
                        sendMessageToServer(data.imageUrl, false, 'image');
                    } else {
                        throw new Error(data.message || 'Image upload failed (server response).');
                    }
                })
                .catch(err => {
                    console.error('Image upload/send process error:', err);
                    alert('Error processing image: ' + err.message);
                })
                .finally(() => {
                    this.value = "";
                });
        });
    }
}
// #endregion

// -----------------------------------------------------------------------------
// #region UI EVENT LISTENERS (Stickers, Emoji, Modals, Audio - adapted from original script.js)
// -----------------------------------------------------------------------------
function setupStaticEventListeners() {
    const conversationWrapper = document.querySelector('#conversation-active-chat .conversation-wrapper');
    const emojiBtn = document.querySelector('.emoji-btn');
    const emojiPicker = document.querySelector('emoji-picker');
    const conversationFormInput = document.querySelector('.conversation-form-input');
    const stickerBtn = document.querySelector('.conversation-form-sticker');
    const stickerPanel = document.getElementById('stickerPanel');
    const recordBtn = document.querySelector('.conversation-form-record');
    const cancelReplyBtn = document.querySelector('.cancel-reply');
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const imageModalClose = document.querySelector('.image-modal-close');
    const conversationBackBtn = document.querySelector('#conversation-active-chat .conversation-back');

    if (conversationBackBtn) {
        conversationBackBtn.addEventListener('click', function (e) {
            e.preventDefault();
            const conversationDiv = document.getElementById('conversation-active-chat');
            const defaultConversationDiv = document.querySelector('.conversation-default');

            if (conversationDiv) {
                conversationDiv.classList.remove('active');
                conversationDiv.style.display = 'none';
            }
            if (defaultConversationDiv) {
                defaultConversationDiv.classList.add('active');
                defaultConversationDiv.style.display = '';
            }
            if (pollingInterval) clearInterval(pollingInterval);
            activeChatUserId = null;
        });
    }

    if (emojiBtn && emojiPicker && conversationFormInput) {
        emojiBtn.addEventListener('click', () => {
            emojiPicker.style.display = emojiPicker.style.display === 'none' || emojiPicker.style.display === '' ? 'block' : 'none';
        });
        emojiPicker.addEventListener('emoji-click', event => {
            conversationFormInput.value += event.detail.unicode;
            conversationFormInput.focus();
            emojiPicker.style.display = 'none';
        });
    }

    if (stickerBtn && stickerPanel) {
        stickerBtn.addEventListener('click', () => {
            if (stickerPanel.style.display === 'none' || stickerPanel.style.display === '') {
                stickerPanel.innerHTML = '';
                STICKERS_LIST.forEach(url => {
                    const img = document.createElement('img');
                    img.src = url;
                    img.style.width = '60px'; img.style.margin = '4px'; img.style.cursor = 'pointer'; img.alt = "Sticker";
                    img.addEventListener('click', () => {
                        sendMessageToServer(url, false, 'sticker');
                        stickerPanel.style.display = 'none';
                    });
                    stickerPanel.appendChild(img);
                });
                stickerPanel.style.display = 'block';
            } else {
                stickerPanel.style.display = 'none';
            }
        });
    }

    if (imageModal && modalImage && imageModalClose && conversationWrapper) {
        conversationWrapper.addEventListener('click', function (e) {
            const imageClicked = e.target.closest('.message-image:not(.sticker-image)');
            if (imageClicked && !e.target.closest('.conversation-item-dropdown')) {
                modalImage.src = imageClicked.src;
                imageModal.style.display = 'flex';
            }
        });
        imageModalClose.addEventListener('click', () => imageModal.style.display = 'none');
        imageModal.addEventListener('click', function (e) { if (e.target === this) this.style.display = 'none'; });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if (imageModal && imageModal.style.display !== 'none') imageModal.style.display = 'none';
            if (stickerPanel && stickerPanel.style.display !== 'none') stickerPanel.style.display = 'none';
            if (emojiPicker && emojiPicker.style.display !== 'none') emojiPicker.style.display = 'none';
        }
    });

    if (cancelReplyBtn) {
        cancelReplyBtn.addEventListener('click', (e) => { e.preventDefault(); clearActiveReplyPreviewGlobal(); });
    }

    if (recordBtn) {
        recordBtn.addEventListener('click', async () => {
            const sendBtn = document.querySelector('.conversation-form-submit');
            if (isRecording) {
                if (mediaRecorder && mediaRecorder.state !== "inactive") {
                    mediaRecorder.stop();
                } else {
                    stopSoundVisualizer(); // Will also attempt to stop tracks
                    recordBtn.classList.remove('is-recording');
                    recordBtn.innerHTML = '<i class="ri-mic-line"></i>';
                    if (sendBtn) sendBtn.style.display = 'flex';
                }
                isRecording = false;
                return;
            }

            if (!activeChatUserId) {
                alert('Please select a chat to send an audio message.'); return;
            }
            try {
                originalStreamForRecorder = await navigator.mediaDevices.getUserMedia({ audio: true });
                let streamForVisualizer = originalStreamForRecorder.clone ? originalStreamForRecorder.clone() : originalStreamForRecorder;

                const recorderOptions = { mimeType: 'audio/webm; codecs=opus' };
                mediaRecorder = MediaRecorder.isTypeSupported(recorderOptions.mimeType)
                    ? new MediaRecorder(originalStreamForRecorder, recorderOptions)
                    : new MediaRecorder(originalStreamForRecorder);

                audioChunks = [];
                startSoundVisualizer(streamForVisualizer); // Sets isRecording to true

                if (sendBtn) sendBtn.style.display = 'none';
                recordBtn.classList.add('is-recording');
                recordBtn.innerHTML = `<span class="recording-indicator-content"><i class="ri-stop-circle-line"></i> <span class="recording-text">Grabando...</span></span>`;

                mediaRecorder.ondataavailable = event => {
                    if (event.data.size > 0) audioChunks.push(event.data);
                };

                mediaRecorder.onstop = () => {
                    // Visualizer and stream stopping is handled by stopSoundVisualizer now if called from there.
                    // If onstop is called directly by mediaRecorder.stop(), ensure visualizer is also stopped.
                    if (isRecording) stopSoundVisualizer();


                    recordBtn.classList.remove('is-recording');
                    recordBtn.innerHTML = '<i class="ri-mic-line"></i>';
                    if (sendBtn) sendBtn.style.display = 'flex';

                    if (audioChunks.length > 0) {
                        const audioBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                        const audioUrl = URL.createObjectURL(audioBlob);
                        sendMessageToServer(audioUrl, false, 'audio');
                    } else {
                        console.log("No audio chunks recorded.");
                    }
                    audioChunks = [];
                };
                mediaRecorder.start(1000);
            } catch (err) {
                console.error('Error accessing mic or starting MediaRecorder:', err);
                alert('Mic access or recording failed: ' + err.message);
                isRecording = false;
                stopSoundVisualizer();
                recordBtn.classList.remove('is-recording');
                recordBtn.innerHTML = '<i class="ri-mic-line"></i>';
                if (sendBtn) sendBtn.style.display = 'flex';
            }
        });
    }

    if (conversationWrapper) {
        conversationWrapper.addEventListener('click', function (e) {
            const dropdownToggle = e.target.closest('.conversation-item-dropdown-toggle');
            if (dropdownToggle) {
                e.preventDefault();
                const parentDropdown = dropdownToggle.closest('.conversation-item-dropdown');
                if (parentDropdown) {
                    document.querySelectorAll('#conversation-active-chat .conversation-item-dropdown.active').forEach(activeDd => {
                        if (activeDd !== parentDropdown) activeDd.classList.remove('active');
                    });
                    parentDropdown.classList.toggle('active');
                }
                return;
            }

            const actionLink = e.target.closest('.conversation-item-dropdown-list a');
            if (actionLink) {
                e.preventDefault();
                const messageId = actionLink.dataset.messageId;
                const messageItem = document.getElementById(`msg-${messageId}`);
                if (actionLink.classList.contains('Delete-btn')) {
                    console.log("Delete message:", messageId);
                    if (messageItem) messageItem.remove(); // TODO: Server-side delete
                } else if (actionLink.classList.contains('edit-btn')) {
                    console.log("Edit message:", messageId);
                    const textEl = messageItem ? messageItem.querySelector('.conversation-item-text p') : null;
                    if (textEl) {
                        const newText = prompt("Edit:", textEl.textContent);
                        if (newText !== null && newText.trim() !== "") {
                            textEl.textContent = newText; // TODO: Server-side update
                        }
                    } else {
                        alert("Solo se pueden editar mensajes de texto.");
                    }
                } else if (actionLink.classList.contains('forward-btn')) {
                    console.log("Forward message:", messageId); // Placeholder for reply/forward logic
                    // Example: Setup reply preview
                    const replyPreviewEl = document.querySelector('.conversation-reply-preview');
                    const replyTextEl = replyPreviewEl.querySelector('.reply-text');
                    if (messageItem && replyPreviewEl && replyTextEl) {
                        let originalContent = "Mensaje adjunto";
                        const textP = messageItem.querySelector('.conversation-item-text p');
                        const imgMsg = messageItem.querySelector('.conversation-item-text .message-image');
                        if (textP) originalContent = textP.textContent.substring(0, 50) + (textP.textContent.length > 50 ? "..." : "");
                        else if (imgMsg) originalContent = "Imagen adjunta";

                        replyTextEl.innerHTML = `Respondiendo a: <i>${originalContent}</i>`;
                        replyPreviewEl.style.display = 'flex';
                        replyingToElement = messageId; // Store ID of message being replied to
                        activeReplyPreview = replyPreviewEl; // From original script
                    }
                }
                if (actionLink.closest('.conversation-item-dropdown')) {
                    actionLink.closest('.conversation-item-dropdown').classList.remove('active');
                }
                return;
            }

            const voiceMsgContainer = e.target.closest('.voice-message');
            if (voiceMsgContainer) {
                if (e.target.closest('.conversation-item-dropdown')) return;
                let audio = voiceMsgContainer.audioInstance;
                const src = voiceMsgContainer.dataset.audioSrc;
                if (!audio && src) {
                    audio = new Audio(src);
                    voiceMsgContainer.audioInstance = audio;
                }
                if (!audio) { console.error("No audio source for voice message."); return; }

                const playBtnIcon = voiceMsgContainer.querySelector('.play-button i');
                const timeLabel = voiceMsgContainer.querySelector('.voice-time');
                const progressBar = voiceMsgContainer.querySelector('.voice-bar');

                if (audio.paused) {
                    document.querySelectorAll('#conversation-active-chat .voice-message audio').forEach(otherAudio => {
                        if (otherAudio !== audio && !otherAudio.paused) {
                            otherAudio.pause();
                            const otherIcon = otherAudio.closest('.voice-message').querySelector('.play-button i');
                            if (otherIcon) otherIcon.className = 'ri-play-fill';
                        }
                    });
                    audio.play().then(() => {
                        if (playBtnIcon) playBtnIcon.className = 'ri-pause-fill';
                    }).catch(error => console.error("Error playing audio:", error));
                    audio.ontimeupdate = () => {
                        if (progressBar && audio.duration) progressBar.style.width = (audio.currentTime / audio.duration) * 100 + '%';
                        if (timeLabel) {
                            const minutes = Math.floor(audio.currentTime / 60);
                            const seconds = Math.floor(audio.currentTime % 60);
                            timeLabel.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                        }
                    };
                    audio.onended = () => {
                        if (playBtnIcon) playBtnIcon.className = 'ri-play-fill';
                        if (progressBar) progressBar.style.width = '0%';
                        // Optionally reset time display to 00:00 or full duration
                        if (timeLabel) timeLabel.textContent = '00:00';
                    };
                } else {
                    audio.pause();
                    if (playBtnIcon) playBtnIcon.className = 'ri-play-fill';
                }
            }
        });
    }

    document.addEventListener('click', function (e) {
        if (!e.target.closest('.conversation-item-dropdown-toggle') && !e.target.closest('.conversation-item-dropdown.active')) {
            document.querySelectorAll('#conversation-active-chat .conversation-item-dropdown.active').forEach(activeDropdown => {
                activeDropdown.classList.remove('active');
            });
        }
        if (stickerPanel && stickerPanel.style.display === 'block' && !e.target.closest('.conversation-form-sticker') && !e.target.closest('#stickerPanel')) {
            stickerPanel.style.display = 'none';
        }
        if (emojiPicker && emojiPicker.style.display === 'block' && !e.target.closest('.emoji-btn') && !e.target.closest('emoji-picker')) {
            emojiPicker.style.display = 'none';
        }
    });
}
// #endregion

// -----------------------------------------------------------------------------
// #region INITIALIZATION
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', initChatApp);
// #endregion