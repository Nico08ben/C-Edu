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
      
      // Extraer el ID del contacto - Usamos el nombre del contacto como identificador temporal
      currentContactId = contact.querySelector("h3").textContent;
      
      // Actualizar encabezado del chat
      const contactName = contact.querySelector("h3").textContent;
      chatHeader.textContent = contactName;
      chatStatus.textContent = "Conectado";
      chatBody.innerHTML = `<p>Chat con ${contactName}</p>`;
      
      // Iniciar polling para obtener mensajes
      if (pollingInterval) clearInterval(pollingInterval);
      fetchMessages();
      pollingInterval = setInterval(fetchMessages, 3000);
    });
  });

  // Enviar mensajes - Evento separado para click y tecla Enter
  sendButton.addEventListener("click", (e) => {
    e.preventDefault(); // Prevenir comportamiento por defecto
    sendMessage();
  });
  
  messageInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") {
      e.preventDefault(); // Prevenir comportamiento por defecto
      sendMessage();
    } else {
      showTypingStatus();
    }
  });

  function sendMessage() {
    const mensaje = messageInput.value;  // Permite espacios
    if (mensaje !== "" && currentContactId) {  // Permitir espacios en blanco
      // Implementación para pruebas locales
      // Para pruebas sin backend, establece debugMode = true
      const debugMode = true;
      
      if (debugMode) {
        console.log("Modo de depuración: Mensaje enviado a " + currentContactId);
        console.log("Contenido del mensaje: " + mensaje);
        
        // Agregar mensaje a la interfaz directamente
        const messageElement = document.createElement("p");
        messageElement.classList.add("sent-message");
        messageElement.textContent = mensaje;
        chatBody.appendChild(messageElement);
        chatBody.scrollTop = chatBody.scrollHeight;
        
        // Limpiar input
        messageInput.value = "";
        return;
      }
      
      // Versión con backend
      fetch("send_message.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id_receptor=${encodeURIComponent(currentContactId)}&mensaje=${encodeURIComponent(mensaje)}`
      })
      .then(response => {
        if (!response.ok) {
          console.error("Error HTTP: " + response.status);
          // Aún así, mostrar el mensaje en la interfaz para mejorar experiencia
          throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.text();
      })
      .then(data => {
        console.log("Respuesta del servidor:", data);
        
        // Limpiar input después de confirmación
        messageInput.value = "";
        
        // Agregar mensaje a la interfaz
        const messageElement = document.createElement("p");
        messageElement.classList.add("sent-message");
        messageElement.textContent = mensaje;
        chatBody.appendChild(messageElement);
        chatBody.scrollTop = chatBody.scrollHeight;
        
        // Refrescar mensajes
        fetchMessages(); 
      })
      .catch(err => {
        console.error("Error al enviar mensaje:", err);
        
        // Aún con error, mostrar el mensaje en la interfaz y limpiar input
        const messageElement = document.createElement("p");
        messageElement.classList.add("sent-message");
        messageElement.textContent = mensaje;
        chatBody.appendChild(messageElement);
        chatBody.scrollTop = chatBody.scrollHeight;
        
        messageInput.value = "";
      });
    }
  }

  function fetchMessages() {
    if (!currentContactId) return;
    
    // CORRECCIÓN: Para pruebas locales, deshabilitar simulación que mostraba mensajes predeterminados
    const useSimulation = false; // Cambiado a false para evitar mensajes predeterminados
    
    if (useSimulation) {
      simulateMessages();
      return;
    }
    
    fetch(`get_messages.php?id_contacto=${encodeURIComponent(currentContactId)}`)
      .then(response => {
        if (!response.ok) {
          throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (!Array.isArray(data)) {
          console.error("La respuesta no es un array:", data);
          return;
        }
        
        // Mantenemos solo el mensaje inicial de chat o mostramos solo los mensajes del servidor
        if (chatBody.innerHTML.includes("Chat con")) {
          // Ya tenemos el mensaje inicial, no hacemos nada
        } else {
          chatBody.innerHTML = "";
        }
        
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
      .catch(err => {
        console.error("Error al obtener mensajes:", err);
      });
  }
  
  function simulateMessages() {
    // CORRECCIÓN: Función modificada para NO mostrar mensajes simulados por defecto
    // Esta función ahora no hace nada para evitar los mensajes predeterminados
    // Solo mantiene el scroll al final del chat
    chatBody.scrollTop = chatBody.scrollHeight;
  }

  function showTypingStatus() {
    chatStatus.textContent = "Escribiendo...";
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
      chatStatus.textContent = "Conectado";
    }, 1000);
  }

  // Implementar funcionalidad para el modal del perfil
  const profileModal = document.getElementById("profile-modal");
  const closeModal = document.querySelector(".close-modal");
  
  // Abrir modal al hacer clic en la imagen de perfil del chat
  document.querySelector(".chat-header img").addEventListener("click", () => {
    const contactName = chatHeader.textContent;
    document.getElementById("profile-name").textContent = contactName;
    document.getElementById("profile-pic").src = document.querySelector(".chat-header img").src;
    document.getElementById("profile-role").textContent = "Docente";
    document.getElementById("profile-status").textContent = "Conectado";
    profileModal.style.display = "flex";
  });
  
  // Cerrar modal
  closeModal.addEventListener("click", () => {
    profileModal.style.display = "none";
  });
  
  // Cerrar modal haciendo clic fuera del contenido
  window.addEventListener("click", (e) => {
    if (e.target === profileModal) {
      profileModal.style.display = "none";
    }
  });
  
  // Seleccionar el primer contacto automáticamente para iniciar
  if (contacts.length > 0) {
    contacts[0].click();
  }
});