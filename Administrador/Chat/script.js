// start: Conversation
// Delegación de eventos para dropdowns
document.querySelector('.conversation-wrapper').addEventListener('click', function(e) {
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
document.addEventListener('click', function(e) {
    if(!e.target.closest('.conversation-item-dropdown')) {
        document.querySelectorAll('.conversation-item-dropdown').forEach(i => {
            i.classList.remove('active')
        })
    }
})

document.querySelectorAll('.conversation-form-input').forEach(function(item) {
    item.addEventListener('input', function() {
        this.rows = this.value.split('\n').length
    })
})

document.querySelectorAll('[data-conversation]').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault()
        document.querySelectorAll('.conversation').forEach(function(i) {
            i.classList.remove('active')
        })
        document.querySelector(this.dataset.conversation).classList.add('active')
    })
})

document.querySelectorAll('.conversation-back').forEach(function(item) {
    item.addEventListener('click', function(e) {
        e.preventDefault()
        this.closest('.conversation').classList.remove('active')
        document.querySelector('.conversation-default').classList.add('active')
    })
})

document.addEventListener('DOMContentLoaded', function() {
    let activeReplyPreview = null
    let replyingToElement = null

    // Delegación de eventos para acciones
    document.querySelector('.conversation-wrapper').addEventListener('click', function(e) {
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

            saveButton.addEventListener('click', function() {
                paragraph.textContent = textarea.value
                messageText.innerHTML = ''
                messageText.appendChild(paragraph)
            })
        }

        // Responder mensaje
        else if (target.classList.contains('forward-btn')) {
            const conversationItem = target.closest('.conversation-item')
            const messageText = conversationItem.querySelector('p').innerText
            const replyPreview = document.querySelector('.conversation-reply-preview')
            
                if (replyPreview) {
                    replyPreview.querySelector('.reply-text').innerText = messageText
                    replyPreview.style.display = 'flex'
                    activeReplyPreview = replyPreview
                    replyingToElement = messageText
                }
           
        }
    })

    // Cancelar respuesta
    document.querySelector('.cancel-reply').addEventListener('click', function(e) {
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
    document.querySelector('.conversation-form-submit').addEventListener('click', function() {
        const input = document.querySelector('.conversation-form-input')
        const message = input.value.trim()
        const replyPreview = document.querySelector('.conversation-reply-preview')
        
        const replyText = replyPreview ? replyPreview.querySelector('.reply-text').innerText : ''
        
        if (message === '') return

        const currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
        let messageHTML = ''

       if (replyText) {
    messageHTML = `
        <li class="conversation-item">
            <div class="conversation-item-side">
                <img class="conversation-item-image" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8cGVvcGxlfGVufDB8fDB8fHww&auto=format&fit=crop&w=500&q=60" alt="">
            </div>
            <div class="conversation-item-content">
                <div class="conversation-item-wrapper">
                    <div class="conversation-item-box">
                        <!-- Aquí movemos la cita arriba del texto -->
                        <div class="conversation-reply-preview" style="margin-bottom: 5px;">
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
                <li class="conversation-item">
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
                        <img src="${imageUrl}" style="max-width: 200px; border-radius: 8px;" />
                        <div class="conversation-item-time">${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
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