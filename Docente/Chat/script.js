document.addEventListener('DOMContentLoaded', () => {
    const contacts = document.querySelectorAll('.contact');
    const chatHeader = document.querySelector('.chat-header h3');
    const chatStatus = document.querySelector('.chat-header .status');
    const chatBody = document.querySelector('.chat-body');
    const messageInput = document.querySelector('.chat-footer input');
    const sendButton = document.querySelector('.chat-footer button');

    contacts.forEach(contact => {
        contact.addEventListener('click', () => {
            document.querySelector('.contact.active')?.classList.remove('active');
            contact.classList.add('active');
            chatHeader.textContent = contact.querySelector('h3').textContent;
            chatStatus.textContent = contact.querySelector('.status')?.textContent || 'Conectado';
            chatBody.innerHTML = `<p>Chat con ${chatHeader.textContent}</p>`;
        });
    });

    sendButton.addEventListener('click', sendMessage);
    messageInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });

    function sendMessage() {
        const message = messageInput.value.trim();
        if (message) {
            const messageElement = document.createElement('p');
            messageElement.textContent = message;
            messageElement.style.textAlign = 'right';
            messageElement.style.background = `var(--primary-color-ligth)`;
            messageElement.style.padding = '5px 10px';
            messageElement.style.borderRadius = '10px';
            messageElement.style.margin = '5px 0';
            messageElement.style.color = `var(--title-color)`;

            chatBody.appendChild(messageElement);
            messageInput.value = '';
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }
});
