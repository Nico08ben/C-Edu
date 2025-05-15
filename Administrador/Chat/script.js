// start: Conversation
// Delegación de eventos para dropdowns
document.querySelector('.conversation-wrapper').addEventListener('click', function (e) {
    const toggle = e.target.closest('.conversation-item-dropdown-toggle')
    if (toggle) {
        e.preventDefault()
        const dropdown = toggle.closest('.conversation-item-dropdown')

        // Cerrar otros dropdowns y alternar el actual
        document.querySelectorAll('.conversation-item-dropdown').forEach(item => {
            if (item !== dropdown) item.classList.remove('active')
        })
        dropdown.classList.toggle('active')
    }
})

// Cerrar dropdowns al hacer clic fuera
document.addEventListener('click', function (e) {
    if (!e.target.closest('.conversation-item-dropdown')) {
        document.querySelectorAll('.conversation-item-dropdown').forEach(i => {
            i.classList.remove('active')
        })
    }
})

document.querySelectorAll('.conversation-form-input').forEach(function (item) {
    item.addEventListener('input', function () {
        this.rows = this.value.split('\n').length
    })
})

document.querySelectorAll('[data-conversation]').forEach(function (item) {
    item.addEventListener('click', function (e) {
        e.preventDefault()
        document.querySelectorAll('.conversation').forEach(function (i) {
            i.classList.remove('active')
        })
        document.querySelector(this.dataset.conversation).classList.add('active')
    })
})

document.querySelectorAll('.conversation-back').forEach(function (item) {
    item.addEventListener('click', function (e) {
        e.preventDefault()
        this.closest('.conversation').classList.remove('active')
        document.querySelector('.conversation-default').classList.add('active')
    })
})

document.addEventListener('DOMContentLoaded', function () {
    const recordBtn = document.querySelector('.conversation-form-record')
    let mediaRecorder
    let audioChunks = []
    let isRecording = false

    recordBtn.addEventListener('click', async () => {
        const sendBtn = document.querySelector('.conversation-form-submit')

        if (isRecording) {
            // Detener grabación
            isRecording = false
            mediaRecorder.stop()
            recordBtn.innerHTML = '<i class="ri-mic-line"></i>'
            sendBtn.style.display = 'flex'
            return
        }

        // Iniciar grabación
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ audio: true })
            mediaRecorder = new MediaRecorder(stream)
            audioChunks = []
            isRecording = true

            // Oculta botón de enviar mientras se graba
            sendBtn.style.display = 'none'

            recordBtn.innerHTML = `<button class="stop-recording-btn" style="
            background-color: red;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 14px;
            cursor: pointer;
        ">🎤 Grabando... Click para detener</button>`

            mediaRecorder.ondataavailable = event => {
                audioChunks.push(event.data)
            }

            mediaRecorder.onstop = () => {
                const audioBlob = new Blob(audioChunks, { type: 'audio/webm' })
                const audioUrl = URL.createObjectURL(audioBlob)
                insertAudioMessage(audioUrl)

                // Restaurar botones
                recordBtn.innerHTML = '<i class="ri-mic-line"></i>'
                sendBtn.style.display = 'flex'
            }

            mediaRecorder.start()
        } catch (err) {
            console.error('Error al acceder al micrófono:', err)
            alert('No se pudo acceder al micrófono.')
        }
    })



    const emojiBtn = document.querySelector('.emoji-btn')
    const emojiPicker = document.querySelector('emoji-picker')
    const input = document.querySelector('.conversation-form-input')

    // Mostrar/ocultar picker
    emojiBtn.addEventListener('click', () => {
        emojiPicker.style.display = emojiPicker.style.display === 'none' ? 'block' : 'none'
    })

    // Insertar emoji al input
    emojiPicker.addEventListener('emoji-click', event => {
        input.value += event.detail.unicode
        input.focus()
    })
    document.querySelector('.conversation-form-input').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault() // evita el salto de línea
            document.querySelector('.conversation-form-submit').click() // simula clic en el botón de enviar
        }
    })
    let activeReplyPreview = null
    let replyingToElement = null

    // Delegación de eventos para acciones
    document.querySelector('.conversation-wrapper').addEventListener('click', function (e) {
        const target = e.target.closest('a')
        if (!target) return
        e.preventDefault()

        // Eliminar mensaje
        if (target.classList.contains('Delete-btn')) {
            const messageItem = target.closest('.conversation-item-wrapper')
            if (messageItem) messageItem.remove()
        }

        // Editar mensaje
        else if (target.classList.contains('edit-btn')) {
            const messageText = target.closest('.conversation-item-wrapper').querySelector('.conversation-item-text')
            const paragraph = messageText.querySelector('p')
            const textarea = document.createElement('textarea')
            textarea.value = paragraph.textContent
            messageText.innerHTML = ''
            messageText.appendChild(textarea)

            const saveButton = document.createElement('button')
            saveButton.textContent = 'Save'
            messageText.appendChild(saveButton)

            saveButton.addEventListener('click', function () {
                paragraph.textContent = textarea.value
                messageText.innerHTML = ''
                messageText.appendChild(paragraph)
            })
        }

        // Responder mensaje
        else if (target.classList.contains('forward-btn')) {
            const conversationItem = target.closest('.conversation-item')
            replyingToElement = conversationItem.id
            const messageBox = conversationItem.querySelector('.conversation-item-text')
            const replyBox = messageBox.querySelector('.reply-box')
            if (replyBox) replyBox.remove()

            const textElement = messageBox.querySelector('p')
            const imgElement = messageBox.querySelector('.message-image')
            const audioElement = messageBox.querySelector('audio')

            let replyContent = ''

            if (audioElement) {
                replyContent = `<p>Audio 🔊</p>`
            } else {
                if (imgElement) {
                    replyContent += `<img src="${imgElement.src}" style="max-width: 100px; max-height: 100px; border-radius: 4px;">`
                }
                if (textElement) {
                    replyContent += `<p>${textElement.innerText}</p>`
                }
            }


            const replyPreview = document.querySelector('.conversation-reply-preview')

            // Asegúrate de que siempre actualice el contenedor del input, no el de un mensaje anterior
            if (replyPreview && !replyPreview.closest('.conversation-item')) {
                replyPreview.querySelector('.reply-text').innerHTML = replyContent
                replyPreview.style.display = 'flex'
                activeReplyPreview = replyPreview
                replyingToElement = conversationItem.id
            }


        }
    })

    // Cancelar respuesta
    document.querySelector('.cancel-reply').addEventListener('click', function (e) {
        e.preventDefault()
        const replyPreview = this.closest('.conversation-reply-preview')
        if (replyPreview) {
            replyPreview.style.display = 'none'
            replyPreview.querySelector('.reply-text').innerText = ''
            replyingToElement = null
            activeReplyPreview = null
        }
    })

    // Enviar mensaje
    document.querySelector('.conversation-form-submit').addEventListener('click', function () {


        const input = document.querySelector('.conversation-form-input')
        const message = input.value.trim()
        const replyPreview = document.querySelector('.conversation-reply-preview')

        const replyText = replyPreview ? replyPreview.querySelector('.reply-text').innerHTML : ''

        if (message === '') return

        const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        const messageId = 'msg-' + Date.now()
        let messageHTML = ''

        if (replyText) {
            messageHTML = `
            <li class="conversation-item" id="${messageId}">
            <div class="conversation-item-side">
                <img class="conversation-item-image" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8cGVvcGxlfGVufDB8fDB8fHww&auto=format&fit=crop&w=500&q=60" alt="">
            </div>
            <div class="conversation-item-content">
                <div class="conversation-item-wrapper">
                    <div class="conversation-item-box">
                        <!-- Aquí movemos la cita arriba del texto -->
                        <div class="reply-box" data-reply-id="${replyingToElement}" style="margin-bottom: 5px; cursor: pointer;">
                        <div class="reply-text">${replyText}</div>
                        </div>
                        <div class="conversation-item-text">
                            <p>${message}</p>
                            <div class="conversation-item-time">${currentTime}</div>
                        </div>
                        <div class="conversation-item-dropdown">
                            <button type="button" class="conversation-item-dropdown-toggle"><i class="ri-more-2-line"></i></button>
                            <ul class="conversation-item-dropdown-list">
                                <li><a href="#" class="edit-btn"><i class="ri-pencil-fill"></i>Edit</a></li>
                                <li><a href="#" class="forward-btn"><i class="ri-share-forward-line"></i>Forward</a></li>
                                <li><a href="#" class="Delete-btn"><i class="ri-delete-bin-line"></i>Delete</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    `
        }
        else {
            messageHTML = `
            <li class="conversation-item" id="${messageId}">
                    <div class="conversation-item-side">
                        <img class="conversation-item-image" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8cGVvcGxlfGVufDB8fDB8fHww&auto=format&fit=crop&w=500&q=60" alt="">
                    </div>
                    <div class="conversation-item-content">
                        <div class="conversation-item-wrapper">
                            <div class="conversation-item-box">
                                <div class="conversation-item-text">
                                    <p>${message}</p>
                                    <div class="conversation-item-time">${currentTime}</div>
                                </div>
                                <div class="conversation-item-dropdown">
                                    <button type="button" class="conversation-item-dropdown-toggle"><i class="ri-more-2-line"></i></button>
                                    <ul class="conversation-item-dropdown-list">
                                        <li><a href="#" class="edit-btn"><i class="ri-pencil-fill"></i>Edit</a></li>
                                        <li><a href="#" class="forward-btn"><i class="ri-share-forward-line"></i>Forward</a></li>
                                        <li><a href="#" class="Delete-btn"><i class="ri-delete-bin-line"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            `
        }
        document.querySelector('.conversation-wrapper').insertAdjacentHTML('beforeend', messageHTML)

        const conversationMain = document.querySelector('.conversation-main')
        conversationMain.scrollTop = conversationMain.scrollHeight

        input.value = ''
        input.rows = 1

        if (replyPreview) {
            replyPreview.style.display = 'none'
            replyPreview.querySelector('.reply-text').innerText = ''
            activeReplyPreview = null
            replyingToElement = null
        }
    })
})
// end: Conversation
document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file || !file.type.startsWith('image/')) {
        alert('Solo se permiten imágenes.');
        return;
    }

    const formData = new FormData();
    formData.append('image', file);

    fetch('upload.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                insertImageMessage(data.imageUrl);
            } else {
                alert('No se pudo subir la imagen.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error al subir la imagen.');
        });
});

function insertImageMessage(imageUrl) {
    const messageList = document.querySelector('#conversation-1 .conversation-wrapper');
    const li = document.createElement('li');
    li.className = 'conversation-item';
    li.innerHTML = `
        <div class="conversation-item-side">
            <img class="conversation-item-image" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?..." alt="">
        </div>
        <div class="conversation-item-content">
            <div class="conversation-item-wrapper">
                <div class="conversation-item-box">
                    <div class="conversation-item-text">
                    <img class="message-image" src="${imageUrl}" style="max-width: 300px; border-radius: 8px; height: 150px;" />
                        <div class="conversation-item-time">${new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                    </div>
                    <div class="conversation-item-dropdown">
                                    <button type="button" class="conversation-item-dropdown-toggle"><i class="ri-more-2-line"></i></button>
                                    <ul class="conversation-item-dropdown-list">
                                        <li><a href="#" class="edit-btn"><i class="ri-pencil-fill"></i>Edit</a></li>
                                        <li><a href="#" class="forward-btn"><i class="ri-share-forward-line"></i>Forward</a></li>
                                        <li><a href="#" class="Delete-btn"><i class="ri-delete-bin-line"></i>Delete</a></li>
                                    </ul>
                                </div>
                </div>
            </div>
        </div>`;
    messageList.appendChild(li); // Inserta el nuevo mensaje en el DOM
    messageList.scrollTop = messageList.scrollHeight; // Asegura que la conversación se desplace al final

}

function insertAudioMessage(audioUrl) {
    const messageList = document.querySelector('.conversation-wrapper')
    const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })

    const li = document.createElement('li')
    li.className = 'conversation-item'
    li.innerHTML = `
        <div class="conversation-item-side">
            <img class="conversation-item-image" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?..." alt="">
        </div>
        <div class="conversation-item-content">
            <div class="conversation-item-wrapper">
                <div class="conversation-item-box">
                    <div class="conversation-item-text">
                        <div class="audio-message-wrapper" style="display: flex; align-items: center; padding: 10px;">
                            <audio controls style="width: 220px;">
                                <source src="${audioUrl}" type="audio/webm">
                                Tu navegador no soporta el elemento de audio.
                            </audio>
                        </div>
                        <div class="conversation-item-time">${currentTime}</div>
                    </div>
                    <div class="conversation-item-dropdown">
                        <button type="button" class="conversation-item-dropdown-toggle"><i class="ri-more-2-line"></i></button>
                        <ul class="conversation-item-dropdown-list">
                            <li><a href="#" class="edit-btn"><i class="ri-pencil-fill"></i>Edit</a></li>
                            <li><a href="#" class="forward-btn"><i class="ri-share-forward-line"></i>Forward</a></li>
                            <li><a href="#" class="Delete-btn"><i class="ri-delete-bin-line"></i>Delete</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `
    messageList.appendChild(li)

    const conversationMain = document.querySelector('.conversation-main')
    conversationMain.scrollTop = conversationMain.scrollHeight
}
document.querySelector('.conversation-wrapper').addEventListener('click', function (e) {
    const replyBox = e.target.closest('.reply-box')
    if (!replyBox) return

    const targetId = replyBox.dataset.replyId
    if (!targetId) return

    const targetMessage = document.getElementById(targetId)
    if (!targetMessage) return

    targetMessage.scrollIntoView({ behavior: 'smooth', block: 'center' })

    // Resaltar el mensaje
    targetMessage.classList.add('highlighted-reply')
    setTimeout(() => {
        targetMessage.classList.remove('highlighted-reply')
    }, 1500)
})


