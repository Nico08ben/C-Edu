// script.js

// -----------------------------------------------------------------------------
// #region VARIABLES GLOBALES Y CONSTANTES
// -----------------------------------------------------------------------------
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
// #region FUNCIONES AUXILIARES Y DE VISUALIZADOR DE SONIDO
// -----------------------------------------------------------------------------

function getCurrentTime() {
    return new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function generateMessageId() {
    return 'msg-' + Date.now();
}

function clearActiveReplyPreview() {
    const replyPreviewEl = document.querySelector('.conversation-reply-preview');
    if (replyPreviewEl) {
        replyPreviewEl.style.display = 'none';
        const replyTextEl = replyPreviewEl.querySelector('.reply-text');
        if (replyTextEl) {
            replyTextEl.innerHTML = '';
        }
    }
    activeReplyPreview = null;
    replyingToElement = null;
}

function appendMessageToConversation(messageHTML) {
    const activeConversationWrapper = document.querySelector('.conversation.active .conversation-wrapper');
    if (!activeConversationWrapper) {
        console.error("No se encontró el contenedor de la conversación activa.");
        return;
    }
    activeConversationWrapper.insertAdjacentHTML('beforeend', messageHTML);
    const conversationMain = activeConversationWrapper.parentElement;
    if (conversationMain && typeof conversationMain.scrollTop !== 'undefined') {
        conversationMain.scrollTop = conversationMain.scrollHeight;
    }
}

function stopAllMicrophoneStreams() {
    if (originalStreamForRecorder && typeof originalStreamForRecorder.getTracks === 'function') {
        originalStreamForRecorder.getTracks().forEach(track => {
            track.stop();
        });
        console.log("Original microphone stream tracks explicitly stopped by stopAllMicrophoneStreams.");
        originalStreamForRecorder = null; // Limpiar la referencia
    }
}


function initializeVisualizerNodes(streamForVisualizer) {
    console.log("Initializing visualizer nodes with stream:", streamForVisualizer);
    if (visualizerSourceNode) {
        visualizerSourceNode.disconnect();
        visualizerSourceNode = null;
    }
    try {
        visualizerSourceNode = visualizerAudioContext.createMediaStreamSource(streamForVisualizer);
        visualizerAnalyser = visualizerAudioContext.createAnalyser();
        visualizerAnalyser.fftSize = 256;
        const bufferLength = visualizerAnalyser.frequencyBinCount;
        visualizerDataArray = new Uint8Array(bufferLength);
        visualizerSourceNode.connect(visualizerAnalyser);
        const visualizerContainer = document.getElementById('sound-visualizer-container');
        if (visualizerContainer) {
            visualizerContainer.innerHTML = '';
            const numberOfBars = 12;
            for (let i = 0; i < numberOfBars; i++) {
                const bar = document.createElement('div');
                visualizerContainer.appendChild(bar);
            }
            console.log(numberOfBars + " visualizer bars created.");
        } else {
            console.error("Visualizer container not found!");
        }
        drawSoundVisualizer();
    } catch (e) {
        console.error("Error creating media stream source for visualizer:", e);
    }
}

function startSoundVisualizer(streamToVisualize) {
    console.log("Attempting to start sound visualizer...");
    if (!visualizerAudioContext || visualizerAudioContext.state === 'closed') {
        visualizerAudioContext = new AudioContext();
        console.log("New AudioContext created (or previous was closed).");
    }
    if (visualizerAudioContext.state === 'suspended') {
        console.log("AudioContext is suspended, attempting to resume...");
        visualizerAudioContext.resume().then(() => {
            console.log("AudioContext resumed successfully!");
            initializeVisualizerNodes(streamToVisualize);
        }).catch(err => {
            console.error("Error resuming AudioContext for visualizer:", err);
        });
    } else {
        console.log("AudioContext state (visualizer):", visualizerAudioContext.state);
        initializeVisualizerNodes(streamToVisualize);
    }
}

function drawSoundVisualizer() {
    if (!isRecording || !visualizerAnalyser || !visualizerDataArray) {
        console.log("drawSoundVisualizer stopped or did not run animation loop. Conditions:", {
            isRecording: isRecording,
            hasAnalyser: !!visualizerAnalyser,
            hasDataArray: !!visualizerDataArray
        });
        if (visualizerRequestFrameId) {
            cancelAnimationFrame(visualizerRequestFrameId);
            visualizerRequestFrameId = null;
        }
        return;
    }
    visualizerRequestFrameId = requestAnimationFrame(drawSoundVisualizer);
    visualizerAnalyser.getByteFrequencyData(visualizerDataArray);
    // console.log("Visualizer Raw Data Sample:", visualizerDataArray[0], visualizerDataArray[10], visualizerDataArray[20]);
    const visualizerContainer = document.getElementById('sound-visualizer-container');
    if (visualizerContainer) {
        const bars = visualizerContainer.children;
        const bufferLength = visualizerAnalyser.frequencyBinCount;
        const containerHeight = 24;
        for (let i = 0; i < bars.length; i++) {
            if (bars[i]) {
                const dataIndex = Math.floor((i * bufferLength) / (bars.length * 3));
                const rawValue = visualizerDataArray[dataIndex];
                const normalizedValue = rawValue / 255;
                let barHeight = Math.pow(normalizedValue, 0.6) * containerHeight;
                barHeight = Math.max(1, barHeight);
                barHeight = Math.min(barHeight, containerHeight);
                bars[i].style.height = `${barHeight}px`;
            }
        }
    }
}

function stopSoundVisualizer() {
    console.log("Stopping sound visualizer...");
    // console.trace(); 
    if (visualizerRequestFrameId) {
        cancelAnimationFrame(visualizerRequestFrameId);
        visualizerRequestFrameId = null;
    }
    if (visualizerSourceNode) {
        visualizerSourceNode.disconnect();
        visualizerSourceNode = null;
    }
    visualizerAnalyser = null;
    visualizerDataArray = null;
    const visualizerContainer = document.getElementById('sound-visualizer-container');
    if (visualizerContainer) {
        visualizerContainer.innerHTML = '';
        console.log("Visualizer container cleared.");
    }
    if (visualizerAudioContext && visualizerAudioContext.state !== 'closed') {
        visualizerAudioContext.close().then(() => {
            console.log("Visualizer AudioContext closed.");
            visualizerAudioContext = null;
        }).catch(err => {
            console.error("Error closing visualizer AudioContext:", err);
            visualizerAudioContext = null;
        });
    } else if (visualizerAudioContext && visualizerAudioContext.state === 'closed') {
         visualizerAudioContext = null;
    }
}
// #endregion

// -----------------------------------------------------------------------------
// #region CREACIÓN DE HTML DE MENSAJES
// -----------------------------------------------------------------------------
// ... (Sin cambios)
function createMessageListItemHTML(id, messageSpecificContentHTML, time, replyData) {
    const avatarHTML = `
        <div class="conversation-item-side">
            <img class="conversation-item-image" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8cGVvcGxlfGVufDB8fDB8fHww&auto=format&fit=crop&w=500&q=60" alt="">
        </div>`;

    let replyBoxHTMLString = '';
    if (replyData && replyData.replyTextContent && replyData.replyingToElementId) {
        replyBoxHTMLString = `
            <div class="reply-box" data-reply-id="${replyData.replyingToElementId}" style="margin-bottom: 5px; cursor: pointer;">
                <div class="reply-text">${replyData.replyTextContent}</div>
            </div>`;
    }

    const dropdownHTML = `
        <div class="conversation-item-dropdown">
            <button type="button" class="conversation-item-dropdown-toggle"><i class="ri-more-2-line"></i></button>
            <ul class="conversation-item-dropdown-list">
                <li><a href="#" class="edit-btn"><i class="ri-pencil-fill"></i>Edit</a></li>
                <li><a href="#" class="forward-btn"><i class="ri-share-forward-line"></i>Forward</a></li>
                <li><a href="#" class="Delete-btn"><i class="ri-delete-bin-line"></i>Delete</a></li>
            </ul>
        </div>`;

    return `
        <li class="conversation-item" id="${id}">
            ${avatarHTML}
            <div class="conversation-item-content">
                <div class="conversation-item-wrapper">
                    <div class="conversation-item-box">
                        ${replyBoxHTMLString}
                        <div class="conversation-item-text">
                            ${messageSpecificContentHTML}
                            <div class="conversation-item-time">${time}</div>
                        </div>
                        ${dropdownHTML}
                    </div>
                </div>
            </div>
        </li>`;
}
// #endregion

// -----------------------------------------------------------------------------
// #region FUNCIONES DE INSERCIÓN DE MENSAJES
// -----------------------------------------------------------------------------
// ... (Sin cambios)
function insertTextMessage(text) {
    const messageId = generateMessageId();
    const time = getCurrentTime();
    let replyData = null;
    const replyPreviewEl = document.querySelector('.conversation-reply-preview');

    if (replyPreviewEl && replyPreviewEl.style.display === 'flex' && replyingToElement) {
        replyData = {
            replyTextContent: replyPreviewEl.querySelector('.reply-text').innerHTML,
            replyingToElementId: replyingToElement
        };
    }

    const messageTextHTML = `<p>${text}</p>`;
    const messageHTML = createMessageListItemHTML(messageId, messageTextHTML, time, replyData);
    appendMessageToConversation(messageHTML);

    if (replyData) {
        clearActiveReplyPreview();
    }
}

function insertImageMessage(imageUrl) {
    const messageId = generateMessageId();
    const time = getCurrentTime();
    let replyData = null;
    const replyPreviewEl = document.querySelector('.conversation-reply-preview');
    let currentReplyingToElementForThisMessage = null;

    if (replyPreviewEl && replyPreviewEl.style.display === 'flex' && replyingToElement) {
        currentReplyingToElementForThisMessage = replyingToElement;
        replyData = {
            replyTextContent: replyPreviewEl.querySelector('.reply-text').innerHTML,
            replyingToElementId: currentReplyingToElementForThisMessage
        };
    }

    const imageContentHTML = `<img class="message-image" src="${imageUrl}" style="max-width: 300px; border-radius: 8px; height: 150px;" />`;
    const messageHTML = createMessageListItemHTML(messageId, imageContentHTML, time, replyData);
    appendMessageToConversation(messageHTML);

    if (replyData && replyingToElement === currentReplyingToElementForThisMessage) {
        clearActiveReplyPreview();
    }
}

function insertStickerMessage(stickerUrl) {
    const messageId = generateMessageId();
    const time = getCurrentTime();
    let replyData = null;
    const replyPreviewEl = document.querySelector('.conversation-reply-preview');
    let currentReplyingToElementForThisMessage = null;

    if (replyPreviewEl && replyPreviewEl.style.display === 'flex' && replyingToElement) {
        currentReplyingToElementForThisMessage = replyingToElement;
        replyData = {
            replyTextContent: replyPreviewEl.querySelector('.reply-text').innerHTML,
            replyingToElementId: currentReplyingToElementForThisMessage
        };
    }

    const stickerContentHTML = `<img class="message-image" src="${stickerUrl}" style="max-width: 180px; height: auto; border-radius: 8px;" />`;
    const messageHTML = createMessageListItemHTML(messageId, stickerContentHTML, time, replyData);
    appendMessageToConversation(messageHTML);

    if (replyData && replyingToElement === currentReplyingToElementForThisMessage) {
        clearActiveReplyPreview();
    }
}

function insertAudioMessage(audioUrl) {
    const messageId = generateMessageId();
    const time = getCurrentTime();
    let replyData = null;
    const replyPreviewEl = document.querySelector('.conversation-reply-preview');
    let currentReplyingToElementForThisMessage = null;

    if (replyPreviewEl && replyPreviewEl.style.display === 'flex' && replyingToElement) {
        currentReplyingToElementForThisMessage = replyingToElement;
        replyData = {
            replyTextContent: replyPreviewEl.querySelector('.reply-text').innerHTML,
            replyingToElementId: currentReplyingToElementForThisMessage
        };
    }
    
    let replyBoxHTMLString = '';
    if (replyData) {
        replyBoxHTMLString = `
            <div class="reply-box" data-reply-id="${replyData.replyingToElementId}" style="margin-bottom: 5px; cursor: pointer;">
                <div class="reply-text">${replyData.replyTextContent}</div>
            </div>`;
    }
    
    const originalAudioDropdownHTML = `
        <div class="conversation-item-dropdown" style="margin-left: -30px;"> 
            <button type="button" class="conversation-item-dropdown-toggle"><i class="ri-more-2-line"></i></button>
            <ul class="conversation-item-dropdown-list">
                <li><a href="#" class="edit-btn"><i class="ri-pencil-fill"></i>Edit</a></li>
                <li><a href="#" class="forward-btn"><i class="ri-share-forward-line"></i>Forward</a></li>
                <li><a href="#" class="Delete-btn"><i class="ri-delete-bin-line"></i>Delete</a></li>
            </ul>
        </div>`;

    const audioMessageSpecificContentHTML = `
        <div class="voice-message" data-audio-src="${audioUrl}" style="background-color: var(--sidebar-color);  padding: 10px 14px; border-radius: 20px; display: flex; align-items: center; gap: 10px; width: fit-content;">
            <button class="play-button" style="border: none; background: #e7bb41; color: white; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="ri-play-fill"></i>
            </button>
            <div class="voice-bar-wrapper" style="background: #d3f4ff; height: 4px; width: 120px; border-radius: 2px; position: relative;">
                <div class="voice-bar" style="background: #00C2FF; height: 100%; width: 0%; transition: width 0.2s;"></div>
            </div>
            <div class="voice-time" style="font-size: 13px; color:var(--slate-950);">00:00</div>
            ${originalAudioDropdownHTML} 
        </div>`;

    const finalAudioMessageHTML = `
        <li class="conversation-item" id="${messageId}">
            <div class="conversation-item-side">
                <img class="conversation-item-image" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8cGVvcGxlfGVufDB8fDB8fHww&auto=format&fit=crop&w=500&q=60" alt="">
            </div>
            <div class="conversation-item-content">
                <div class="conversation-item-wrapper">
                    <div class="conversation-item-box" style="background: transparent; box-shadow: none; padding: 0;">
                        ${replyBoxHTMLString}
                        ${audioMessageSpecificContentHTML}
                        <div class="conversation-item-time" style="margin-top: 4px;">${time}</div>
                    </div>
                </div>
            </div>
        </li>`;

    appendMessageToConversation(finalAudioMessageHTML);

    if (replyData && replyingToElement === currentReplyingToElementForThisMessage) {
        clearActiveReplyPreview();
    }
}
// #endregion

// -----------------------------------------------------------------------------
// #region MANEJADORES DE EVENTOS Y LÓGICA DE UI INICIAL
// -----------------------------------------------------------------------------
// ... (Sin cambios)
document.querySelectorAll('.conversation-form-input').forEach(function (item) {
    item.addEventListener('input', function () {
        this.rows = this.value.split('\n').length;
    });
});
document.querySelectorAll('[data-conversation]').forEach(function (item) {
    item.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelectorAll('.conversation').forEach(function (i) {
            i.classList.remove('active');
        });
        const targetConversation = document.querySelector(this.dataset.conversation);
        if (targetConversation) {
            targetConversation.classList.add('active');
        }
    });
});
document.querySelectorAll('.conversation-back').forEach(function (item) {
    item.addEventListener('click', function (e) {
        e.preventDefault();
        const currentConversation = this.closest('.conversation');
        if (currentConversation) {
            currentConversation.classList.remove('active');
        }
        const defaultConversation = document.querySelector('.conversation-default');
        if (defaultConversation) {
            defaultConversation.classList.add('active');
        }
    });
});
// #endregion

// -----------------------------------------------------------------------------
// #region LÓGICA PRINCIPAL (DOMContentLoaded)
// -----------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {
    const conversationWrapper = document.querySelector('.conversation-wrapper');
    const conversationFormInput = document.querySelector('.conversation-form-input');
    const conversationFormSubmit = document.querySelector('.conversation-form-submit');
    const imageInput = document.getElementById('imageInput');
    const emojiBtn = document.querySelector('.emoji-btn');
    const emojiPicker = document.querySelector('emoji-picker');
    const stickerBtn = document.querySelector('.conversation-form-sticker');
    const stickerPanel = document.getElementById('stickerPanel');
    const recordBtn = document.querySelector('.conversation-form-record');
    const cancelReplyBtn = document.querySelector('.cancel-reply');
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const imageModalClose = document.querySelector('.image-modal-close');

    // --- Reproducción de Mensajes de Voz y Modal de Imagen (en conversationWrapper) ---
    // ... (Sin cambios, incluye los console.log de depuración para el botón de play)
    if (conversationWrapper) {
        conversationWrapper.addEventListener('click', function (e) {
            const voiceMsg = e.target.closest('.voice-message');
            // console.log("Voice message play clicked. Target:", e.target); 
            // console.log("Found .voice-message element:", voiceMsg); 

            if (voiceMsg && !e.target.closest('.conversation-item-dropdown')) { 
                // console.log("Processing voice message play..."); 
                let audio = voiceMsg.audioInstance;
                // console.log("Initial audioInstance:", audio); 

                if (!audio) {
                    const src = voiceMsg.dataset.audioSrc;
                    // console.log("Audio source from data-audio-src:", src); 
                    if (src) {
                        audio = new Audio(src);
                        voiceMsg.audioInstance = audio; 
                        // console.log("New Audio object created:", audio); 
                    }
                }

                if (!audio) {
                    console.error("Audio object could not be created or found."); 
                    return; 
                }

                const playBtnIcon = voiceMsg.querySelector('.play-button i');
                const timeLabel = voiceMsg.querySelector('.voice-time');
                const bar = voiceMsg.querySelector('.voice-bar');

                if (audio.paused) {
                    // console.log("Audio is paused, attempting to play..."); 
                    audio.play().then(() => {
                        // console.log("Audio playback started."); 
                        if(playBtnIcon) playBtnIcon.className = 'ri-pause-fill'; 
                    }).catch(error => {
                        console.error("Error playing audio:", error); 
                    });
                    
                    audio.ontimeupdate = () => {
                        if (!audio.duration) return; 
                        const percent = (audio.currentTime / audio.duration) * 100;
                        if(bar) bar.style.width = percent + '%';
                        const sec = Math.floor(audio.currentTime);
                        if(timeLabel) timeLabel.textContent = '00:' + String(sec).padStart(2, '0');
                    };
                    audio.onended = () => {
                        if(playBtnIcon) playBtnIcon.className = 'ri-play-fill';
                        if(bar) bar.style.width = '0%';
                        if(timeLabel) timeLabel.textContent = '00:00';
                    };
                } else {
                    // console.log("Audio is playing, attempting to pause..."); 
                    audio.pause();
                    if(playBtnIcon) playBtnIcon.className = 'ri-play-fill'; 
                    // console.log("Audio paused."); 
                }
            } else if (voiceMsg) {
                // console.log("Click on voice message, but likely on its dropdown."); 
            }

            const imageClicked = e.target.closest('.message-image');
            if (imageClicked && imageModal && modalImage) { 
                if(!e.target.closest('.conversation-item-dropdown')){
                    imageModal.style.display = 'flex';
                    modalImage.src = imageClicked.src;
                }
            }
        });
    }


    // --- Panel de Stickers ---
    // ... (sin cambios)
    if (stickerBtn && stickerPanel) {
        stickerBtn.addEventListener('click', () => {
            if (stickerPanel.style.display === 'none' || stickerPanel.style.display === '') {
                stickerPanel.innerHTML = '';
                STICKERS_LIST.forEach(url => {
                    const img = document.createElement('img');
                    img.src = url;
                    img.style.width = '60px';
                    img.style.margin = '4px';
                    img.style.cursor = 'pointer';
                    img.alt = "Sticker";
                    img.addEventListener('click', () => {
                        insertStickerMessage(url);
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
    
    // --- Cerrar Modales y Paneles ---
    // ... (sin cambios)
    if (imageModalClose) {
        imageModalClose.addEventListener('click', () => {
            if(imageModal) imageModal.style.display = 'none';
        });
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            if(imageModal && imageModal.style.display !== 'none') imageModal.style.display = 'none';
            if(stickerPanel && stickerPanel.style.display !== 'none') stickerPanel.style.display = 'none';
            if(emojiPicker && emojiPicker.style.display !== 'none') emojiPicker.style.display = 'none';
        }
    });
    if (imageModal) {
        imageModal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    }

    // --- Grabación de Audio ---
    if (recordBtn) {
        recordBtn.addEventListener('click', async () => {
            const sendBtn = document.querySelector('.conversation-form-submit');
            if (isRecording) {
                isRecording = false; 
                if (mediaRecorder && mediaRecorder.state !== "inactive") {
                    mediaRecorder.stop(); 
                } else {
                    stopSoundVisualizer(); 
                    stopAllMicrophoneStreams(); // <-- LLAMADA A LA NUEVA FUNCIÓN
                    recordBtn.classList.remove('is-recording');
                    recordBtn.innerHTML = '<i class="ri-mic-line"></i>';
                    if (sendBtn) sendBtn.style.display = 'flex';
                }
                return;
            }
            try {
                originalStreamForRecorder = await navigator.mediaDevices.getUserMedia({ audio: true });
                
                let streamForVisualizer;
                if (typeof originalStreamForRecorder.clone === 'function') {
                    streamForVisualizer = originalStreamForRecorder.clone();
                    console.log("Stream cloned for visualizer.");
                } else {
                    streamForVisualizer = originalStreamForRecorder; 
                    console.log("Stream clone not available, using original for visualizer.");
                }
                
                const recorderOptions = { mimeType: 'audio/webm; codecs=opus' };
                if (MediaRecorder.isTypeSupported(recorderOptions.mimeType)) {
                    mediaRecorder = new MediaRecorder(originalStreamForRecorder, recorderOptions);
                } else {
                    console.warn("Opus codec for webm not supported, falling back.");
                    mediaRecorder = new MediaRecorder(originalStreamForRecorder); 
                }
                
                audioChunks = []; 
                isRecording = true;

                startSoundVisualizer(streamForVisualizer);

                if (sendBtn) sendBtn.style.display = 'none';

                recordBtn.classList.add('is-recording');
                recordBtn.innerHTML = `
                    <span class="recording-indicator-content">
                        <i class="ri-stop-circle-line"></i> 
                        <span class="recording-text">Grabando...</span>
                    </span>`;

                mediaRecorder.ondataavailable = event => {
                    console.log("MediaRecorder ondataavailable, chunk size:", event.data.size);
                    if (event.data.size > 0) {
                        audioChunks.push(event.data);
                    }
                };

                mediaRecorder.onstop = () => {
                    console.log("MediaRecorder.onstop triggered. Audio chunks length:", audioChunks.length);
                    
                    if (audioChunks.length > 0) {
                        const audioBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType || 'audio/webm' });
                        const audioUrl = URL.createObjectURL(audioBlob);
                        console.log("Audio Blob URL:", audioUrl, "Size:", audioBlob.size);
                        insertAudioMessage(audioUrl);
                    } else {
                        console.log("No audio chunks recorded.");
                    }
                    
                    stopSoundVisualizer(); 
                    stopAllMicrophoneStreams(); // <-- LLAMADA A LA NUEVA FUNCIÓN

                    recordBtn.classList.remove('is-recording');
                    recordBtn.innerHTML = '<i class="ri-mic-line"></i>';
                    if (sendBtn) sendBtn.style.display = 'flex';
                };
                
                mediaRecorder.start(1000); 
                console.log("MediaRecorder started with 1s timeslice.");

            } catch (err) {
                console.error('Error al acceder al micrófono o iniciar MediaRecorder:', err);
                alert('No se pudo acceder al micrófono o iniciar la grabación.');
                
                isRecording = false; 
                stopSoundVisualizer(); 
                stopAllMicrophoneStreams(); // <-- LLAMADA A LA NUEVA FUNCIÓN
                
                recordBtn.classList.remove('is-recording');
                recordBtn.innerHTML = '<i class="ri-mic-line"></i>';
                if (sendBtn) sendBtn.style.display = 'flex';
            }
        });
    }

    // --- Emoji Picker ---
    // ... (sin cambios)
     if (emojiBtn && emojiPicker && conversationFormInput) {
        emojiBtn.addEventListener('click', () => {
            emojiPicker.style.display = emojiPicker.style.display === 'none' || emojiPicker.style.display === '' ? 'block' : 'none';
        });
        emojiPicker.addEventListener('emoji-click', event => {
            conversationFormInput.value += event.detail.unicode;
            conversationFormInput.focus();
        });
    }

    // --- Enviar Mensaje con Enter ---
    // ... (sin cambios)
    if (conversationFormInput) {
        conversationFormInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                if(conversationFormSubmit) conversationFormSubmit.click();
            }
        });
    }
    
    // --- Acciones de Mensaje (Editar, Responder, Eliminar) en conversationWrapper ---
    // ... (sin cambios)
    if (conversationWrapper) {
        conversationWrapper.addEventListener('click', function (e) {
            const targetLink = e.target.closest('a');
            if (!targetLink) return;

            if (e.target.closest('.play-button')) return; 

            const messageWrapper = targetLink.closest('.conversation-item-wrapper');
            const conversationItem = targetLink.closest('.conversation-item');

            if (targetLink.classList.contains('Delete-btn')) {
                e.preventDefault();
                if (messageWrapper) {
                    const itemContent = messageWrapper.parentElement;
                    messageWrapper.remove();
                    if (itemContent && itemContent.querySelectorAll('.conversation-item-wrapper').length === 0) {
                        if(conversationItem) conversationItem.remove();
                    }
                }
            }
            else if (targetLink.classList.contains('edit-btn')) {
                e.preventDefault();
                if (messageWrapper) {
                    const messageTextEl = messageWrapper.querySelector('.conversation-item-text p');
                    if (messageTextEl) {
                        const currentText = messageTextEl.textContent;
                        const newText = prompt("Editar mensaje:", currentText);
                        if (newText !== null && newText.trim() !== "") {
                            messageTextEl.textContent = newText;
                        }
                    } else {
                        alert("Solo se pueden editar mensajes de texto.");
                    }
                }
            }
            else if (targetLink.classList.contains('forward-btn')) {
                e.preventDefault();
                if (conversationItem) {
                    replyingToElement = conversationItem.id;
                    let replyContent = '';
                    const textElement = conversationItem.querySelector('.conversation-item-text p');
                    const imgElement = conversationItem.querySelector('.conversation-item-text .message-image');
                    const audioWrapper = conversationItem.querySelector('.voice-message');
                    if (audioWrapper) {
                        replyContent = `<p style="font-weight:bold; color:#007bff;">Nota de voz 🔊</p>`;
                    } else if (imgElement) {
                        replyContent = `<img src="${imgElement.src}" style="max-width:50px; max-height:50px; border-radius:4px; margin-right:5px;" alt="Imagen adjunta"> Contenido de imagen`;
                    } else if (textElement) {
                        replyContent = `<p>${textElement.innerText.substring(0, 50)}${textElement.innerText.length > 50 ? '...' : ''}</p>`;
                    } else {
                         replyContent = `<p>Mensaje adjunto</p>`;
                    }
                    const replyPreviewEl = document.querySelector('.conversation-reply-preview');
                    if (replyPreviewEl) {
                        const replyTextEl = replyPreviewEl.querySelector('.reply-text');
                        if (replyTextEl) replyTextEl.innerHTML = replyContent;
                        replyPreviewEl.style.display = 'flex';
                        activeReplyPreview = replyPreviewEl;
                    }
                }
            }
        });
    }
    
    // --- Cancelar Respuesta ---
    // ... (sin cambios)
    if (cancelReplyBtn) {
        cancelReplyBtn.addEventListener('click', function (e) {
            e.preventDefault();
            clearActiveReplyPreview();
        });
    }

    // --- Enviar Mensaje de Texto (Botón Submit) ---
    // ... (sin cambios)
    if (conversationFormSubmit && conversationFormInput) {
        conversationFormSubmit.addEventListener('click', function () {
            const messageText = conversationFormInput.value.trim();
            const replyPreviewEl = document.querySelector('.conversation-reply-preview');
            if (messageText === '' && !(replyPreviewEl && replyPreviewEl.style.display === 'flex')) {
                 return;
            }
            insertTextMessage(messageText);
            conversationFormInput.value = '';
            conversationFormInput.rows = 1;
        });
    }

    // --- Subir Imagen ---
    // ... (sin cambios)
     if (imageInput) {
        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (!file) { this.value = ""; return; }
            if (!file.type.startsWith('image/')) {
                alert('Solo se permiten imágenes.');
                this.value = "";
                return;
            }
            const formData = new FormData();
            formData.append('image', file);
            fetch('upload.php', { method: 'POST', body: formData })
                .then(res => {
                    if (!res.ok) {
                        return res.text().then(text => { throw new Error(`Error del servidor: ${res.status} ${res.statusText}. Respuesta: ${text}`); });
                    }
                    return res.json();
                })
                .then(data => {
                    if (data.success) {
                        insertImageMessage(data.imageUrl);
                    } else {
                        alert(data.message || 'No se pudo subir la imagen (respuesta del servidor).');
                    }
                })
                .catch(err => {
                    console.error('Detalle del error de subida:', err);
                    alert('Error al subir la imagen. ' + (err.message ? err.message : 'Verifique la consola para más detalles.'));
                })
                .finally(() => {
                    this.value = "";
                });
        });
    }

    // --- Navegar a Mensaje Respondido (en conversationWrapper) ---
    // ... (sin cambios)
    if (conversationWrapper) {
        conversationWrapper.addEventListener('click', function (e) {
            const replyBoxClicked = e.target.closest('.reply-box');
            if (!replyBoxClicked) return;

            if (e.target.closest('.conversation-item-dropdown-list a')) return;

            const targetId = replyBoxClicked.dataset.replyId;
            if (!targetId) return;
            const targetMessage = document.getElementById(targetId);
            if (!targetMessage) return;
            targetMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            targetMessage.classList.add('highlighted-reply');
            setTimeout(() => {
                targetMessage.classList.remove('highlighted-reply');
            }, 1500);
        });
    }

}); // Fin de DOMContentLoaded


// -----------------------------------------------------------------------------
// #region MANEJADOR GLOBAL DE CLICS (Dropdowns y cierre de paneles)
// -----------------------------------------------------------------------------
// ... (sin cambios)
document.addEventListener('click', function (e) {
    const dropdownToggle = e.target.closest('.conversation-item-dropdown-toggle');
    const clickedWithinAnyDropdown = e.target.closest('.conversation-item-dropdown');

    if (dropdownToggle) {
        e.preventDefault();
        const parentDropdown = dropdownToggle.closest('.conversation-item-dropdown');
        if (parentDropdown) {
            document.querySelectorAll('.conversation-item-dropdown.active').forEach(activeDropdown => {
                if (activeDropdown !== parentDropdown) {
                    activeDropdown.classList.remove('active');
                }
            });
            parentDropdown.classList.toggle('active');
        }
    } else if (!clickedWithinAnyDropdown) {
        document.querySelectorAll('.conversation-item-dropdown.active').forEach(activeDropdown => {
            activeDropdown.classList.remove('active');
        });
        
        const stickerPanel = document.getElementById('stickerPanel');
        const emojiPicker = document.querySelector('emoji-picker');

        if (stickerPanel && stickerPanel.style.display === 'block' && !e.target.closest('.conversation-form-sticker') && !e.target.closest('#stickerPanel')) {
            stickerPanel.style.display = 'none';
        }
        if (emojiPicker && emojiPicker.style.display === 'block' && !e.target.closest('.emoji-btn') && !e.target.closest('emoji-picker')) {
            emojiPicker.style.display = 'none';
        }
    }
});
// #endregion