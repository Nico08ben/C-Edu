document.addEventListener("DOMContentLoaded", () => {
    const contacts = document.querySelectorAll(".contact");
    const chatHeader = document.querySelector(".chat-header h3");
    const chatStatus = document.querySelector(".chat-header .status");
    const chatBody = document.querySelector(".chat-body");
    const messageInput = document.querySelector(".chat-footer input");
    const sendButton = document.querySelector(".chat-footer button");
    let typingTimeout;

    chatBody.innerHTML = `<p>Chat con ${chatHeader.textContent}</p>`;

    // Manejar la selección de contactos
    contacts.forEach((contact) => {
        contact.addEventListener("click", () => {
            document.querySelector(".contact.active")?.classList.remove("active");
            contact.classList.add("active");
            chatHeader.textContent = contact.querySelector("h3").textContent;
            chatStatus.textContent =
                contact.querySelector(".status")?.textContent || "Conectado";
            chatBody.innerHTML = `<p>Chat con ${chatHeader.textContent}</p>`;
        });
    });

    // Enviar mensajes
    sendButton.addEventListener("click", sendMessage);
    messageInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") sendMessage();
        else showTypingStatus();
    });

    function sendMessage() {
        const message = messageInput.value.trim();
        if (message) {
            const messageElement = document.createElement("p");
            messageElement.textContent = message;
            messageElement.classList.add("sent-message");

            chatBody.appendChild(messageElement);
            messageInput.value = "";
            chatBody.scrollTop = chatBody.scrollHeight;
            chatStatus.textContent = "Conectado";

            // Simulación de mensaje recibido tras 1 segundo
            setTimeout(receiveMessage, 1000);
        }
    }

    function receiveMessage() {
        const messageElement = document.createElement("p");
        messageElement.textContent = "Esto es una respuesta";
        messageElement.classList.add("received-message");

        chatBody.appendChild(messageElement);
        chatBody.scrollTop = chatBody.scrollHeight;
    }

    function showTypingStatus() {
        chatStatus.textContent = "Escribiendo...";
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            chatStatus.textContent = "Conectado";
        }, 1000);
    }

    // ---------- MODAL DEL PERFIL ----------
    const profileModal = document.createElement("div");
    profileModal.id = "profile-modal";
    profileModal.className = "modal";
    profileModal.innerHTML = `
    <div class="modal-content">
        <span class="close-modal" id="close-modal">&times;</span>
        <img id="profile-pic" src="" alt="Foto de perfil">
        <h2 id="profile-name"></h2>
        <p id="profile-username"></p>
        <p id="profile-email"></p>
        <p id="profile-birthday"></p>
        <p id="profile-role">Materia/Rol: </p>
        <p id="profile-phone"></p>
        <p id="profile-group"></p>
        <p id="profile-school"></p>
    </div>`;

    // Agregarlo al body
    document.body.appendChild(profileModal);

    // Mostrar perfil al hacer click en el chat header
    chatHeader.addEventListener("click", () => {
        const activeContact = document.querySelector(".contact.active");
        if (activeContact) {
            document.getElementById("profile-pic").src =
                activeContact.querySelector("img").src;
            document.getElementById("profile-name").textContent =
                activeContact.querySelector("h3").textContent;
            document.getElementById(
                "profile-username"
            ).textContent = `Usuario: ${activeContact.getAttribute("data-username")}`;
            document.getElementById(
                "profile-email"
            ).textContent = `Email: ${activeContact.getAttribute("data-email")}`;
            document.getElementById(
                "profile-birthday"
            ).textContent = `Nacimiento: ${activeContact.getAttribute(
                "data-birthday"
            )}`;
            document.getElementById(
                "profile-role"
            ).textContent = `Materia/Rol: ${activeContact.getAttribute("data-role")}`;
            document.getElementById(
                "profile-phone"
            ).textContent = `Teléfono: ${activeContact.getAttribute("data-phone")}`;
            document.getElementById(
                "profile-group"
            ).textContent = `Grupo: ${activeContact.getAttribute("data-group")}`;
            document.getElementById(
                "profile-school"
            ).textContent = `Colegio: ${activeContact.getAttribute("data-school")}`;
            profileModal.style.display = "block";
        }
    });

    // Cerrar el modal
    document.getElementById('close-modal').addEventListener('click', () => {
        profileModal.style.display = 'none';
    });

    // Cerrar si se hace clic afuera del contenido
    window.addEventListener("click", (e) => {
        if (e.target === profileModal) profileModal.style.display = "none";
    });
});
