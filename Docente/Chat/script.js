document.addEventListener("DOMContentLoaded", () => {
    const contacts = document.querySelectorAll(".contact");
    const chatHeader = document.querySelector(".chat-header h3");
    const chatStatus = document.querySelector(".chat-header .status");
    const chatBody = document.querySelector(".chat-body");
    const messageInput = document.querySelector(".chat-footer input");
    const sendButton = document.querySelector(".chat-footer button");
    let currentContactId = null;
    let typingTimeout;
    let pollingInterval;
  
    // Manejar la selección de contactos
    contacts.forEach((contact) => {
      contact.addEventListener("click", () => {
        document.querySelector(".contact.active")?.classList.remove("active");
        contact.classList.add("active");
        currentContactId = contact.getAttribute("data-id");
        chatHeader.textContent = contact.getAttribute("data-nombre");
        chatStatus.textContent = "Conectado";
        chatBody.innerHTML = `<p>Chat con ${chatHeader.textContent}</p>`;
        
        // Iniciar polling para obtener mensajes
        if (pollingInterval) clearInterval(pollingInterval);
        fetchMessages();
        pollingInterval = setInterval(fetchMessages, 3000);
      });
    });
  
    // Enviar mensajes
    sendButton.addEventListener("click", sendMessage);
    messageInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") sendMessage();
      else showTypingStatus();
    });
  
    function sendMessage() {
      const mensaje = messageInput.value.trim();
      if (mensaje && currentContactId) {
        fetch("send_message.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `id_receptor=${encodeURIComponent(currentContactId)}&mensaje=${encodeURIComponent(mensaje)}`
        })
        .then(response => response.text())
        .then(data => {
          console.log(data);
          messageInput.value = "";
          fetchMessages(); // refrescar mensajes
        })
        .catch(err => console.error(err));
      }
    }
  
    function fetchMessages() {
      if (!currentContactId) return;
      fetch(`get_messages.php?id_contacto=${encodeURIComponent(currentContactId)}`)
        .then(response => response.json())
        .then(data => {
          chatBody.innerHTML = "";
          data.forEach(msg => {
            const messageElement = document.createElement("p");
            // Si el id_emisor es igual al id del contacto, es un mensaje recibido
            if (msg.id_emisor == currentContactId) {
              messageElement.classList.add("received-message");
            } else {
              messageElement.classList.add("sent-message");
            }
            messageElement.textContent = msg.mensaje;
            chatBody.appendChild(messageElement);
          });
          chatBody.scrollTop = chatBody.scrollHeight;
        })
        .catch(err => console.error(err));
    }
  
    function showTypingStatus() {
      chatStatus.textContent = "Escribiendo...";
      clearTimeout(typingTimeout);
      typingTimeout = setTimeout(() => {
        chatStatus.textContent = "Conectado";
      }, 1000);
    }
  
    // Aquí puedes incluir el código para el modal del perfil (si deseas mostrar datos adicionales)
  });
  