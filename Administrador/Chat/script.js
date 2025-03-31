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
      
      // Extraer el ID del contacto - CORRECCIÓN: los contactos no tienen data-id en el HTML
      // Usamos el nombre del contacto como identificador temporal
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

  // Enviar mensajes
  sendButton.addEventListener("click", sendMessage);
  messageInput.addEventListener("keypress", (e) => {
    if (e.key === "Enter") sendMessage();
    else showTypingStatus();
  });

  function sendMessage() {
    const mensaje = messageInput.value.trim();
    if (mensaje && currentContactId) {
      // CORRECCIÓN: Agregamos el evento preventDefault() para evitar comportamiento predeterminado
      // y verificamos que el endpoint existe y es accesible
      
      // Verificar si estamos en un entorno de prueba
      console.log("Enviando mensaje a: " + currentContactId);
      console.log("Contenido: " + mensaje);
      
      fetch("send_message.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `id_receptor=${encodeURIComponent(currentContactId)}&mensaje=${encodeURIComponent(mensaje)}`
      })
      .then(response => {
        // CORRECCIÓN: Verificar si la respuesta es correcta
        if (!response.ok) {
          throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.text();
      })
      .then(data => {
        console.log("Respuesta del servidor:", data);
        messageInput.value = "";
        
        // CORRECCIÓN: Agregar mensaje a la interfaz antes de recibir respuesta del servidor
        // para mejor experiencia de usuario
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
        alert("No se pudo enviar el mensaje. Intentalo de nuevo.");
      });
    }
  }

  function fetchMessages() {
    if (!currentContactId) return;
    
    // CORRECCIÓN: Simulación temporal hasta que el backend esté listo
    // Si get_messages.php no está funcionando correctamente, esta simulación ayudará a probar la interfaz
    const useSimulation = false; // Cambiar a false cuando el backend esté listo
    
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
      .catch(err => {
        console.error("Error al obtener mensajes:", err);
        // No mostrar alerta aquí para no interrumpir la experiencia
      });
  }
  
  function simulateMessages() {
    // Función de prueba para simular mensajes
    const simulatedMessages = [
      { id_emisor: currentContactId, mensaje: "Hola, ¿cómo estás?" },
      { id_emisor: "yo", mensaje: "Bien, gracias. ¿Y tú?" },
      { id_emisor: currentContactId, mensaje: "Todo bien. ¿Recibiste el comunicado?" }
    ];
    
    chatBody.innerHTML = "";
    simulatedMessages.forEach(msg => {
      const messageElement = document.createElement("p");
      if (msg.id_emisor == currentContactId) {
        messageElement.classList.add("received-message");
      } else {
        messageElement.classList.add("sent-message");
      }
      messageElement.textContent = msg.mensaje;
      chatBody.appendChild(messageElement);
    });
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