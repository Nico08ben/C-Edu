document.addEventListener("DOMContentLoaded", function () {
    const editProfileText = document.getElementById("edit-profile");
    const helpText = document.getElementById("help");
    const profileContainer = document.querySelector(".profile-container");
    const ayuda = document.querySelector(".ayuda");
    const editImageButton = document.querySelector(".edit-btn");
    const profileImage = document.querySelector(".user-avatar img");
    const fileInput = document.getElementById("profile-image-input");
    const uploadForm = document.getElementById("upload-form");
    const uploadStatus = document.getElementById("upload-status");
    
    // Configuración inicial: mostrar perfil, ocultar ayuda
    profileContainer.style.display = "flex";
    ayuda.style.display = "none";
    editProfileText.style.fontWeight = "bold";
    helpText.style.fontWeight = "normal";

    // Ocultar perfil al hacer clic en "Ayuda"
    helpText.addEventListener("click", function () {
        profileContainer.style.display = "none";
        ayuda.style.display = "block";
        helpText.style.fontWeight = "bold"; // Resaltar "Ayuda"
        editProfileText.style.fontWeight = "normal"; // Normalizar "Editar Perfil"
    });

    // Mostrar perfil al hacer clic en "Editar Perfil"
    editProfileText.addEventListener("click", function () {
        profileContainer.style.display = "flex";
        ayuda.style.display = "none";
        editProfileText.style.fontWeight = "bold"; // Resaltar "Editar Perfil"
        helpText.style.fontWeight = "normal"; // Normalizar "Ayuda"
    });

    // Abrir el selector de archivos al hacer clic en "EDITAR"
    editImageButton.addEventListener("click", function () {
        fileInput.click();
    });

    // Manejar la selección de archivos
    fileInput.addEventListener("change", function () {
        if (fileInput.files && fileInput.files[0]) {
            // Mostrar una vista previa de la imagen seleccionada
            const reader = new FileReader();
            reader.onload = function (e) {
                profileImage.src = e.target.result;
                
                // Subir la imagen automáticamente
                uploadImage();
            };
            reader.readAsDataURL(fileInput.files[0]);
        }
    });
 // Función para subir la imagen seleccionada
 function uploadImage() {
    const formData = new FormData(uploadForm);
    
    uploadStatus.textContent = "Subiendo imagen...";
    uploadStatus.style.color = "blue";
    uploadStatus.style.display = "block";
    
    // Ajusta esta URL para que apunte a tu script PHP de subida de fotos de perfil
    fetch("../../src/includes/upload_profile_image.php", { // Ruta relativa desde UserProfile/index.js
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            // Intentar obtener más detalles del error si es posible
            return response.json().then(errData => {
                throw new Error(errData.message || 'Error en la respuesta del servidor: ' + response.status);
            }).catch(() => {
                 throw new Error('Error en la respuesta del servidor: ' + response.status);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            uploadStatus.textContent = data.message || "¡Imagen actualizada!";
            uploadStatus.style.color = "green";
            
            // Actualizar la imagen de perfil en la barra lateral (user_info.php) si existe
            const sidebarProfileImage = document.querySelector('#user-profile-box .profile-image-link img');
            if (sidebarProfileImage && data.imageUrl) {
                // Asumimos que data.imageUrl es la ruta relativa desde la raíz del proyecto.
                // Necesitamos construir la URL completa o la ruta correcta para el src.
                // Si imageUrlForDb es 'uploads/profile_pictures/user_1_xxxx.jpg'
                // y la página está en /C-Edu/Administrador/UserProfile/,
                // la ruta relativa correcta al archivo de imagen sería '../../uploads/profile_pictures/user_1_xxxx.jpg'
                // O si tienes una URL base configurada: `urlBase + data.imageUrl`
                sidebarProfileImage.src = '../../' + data.imageUrl + '?t=' + new Date().getTime(); // Añadir timestamp para evitar caché
            }


            setTimeout(() => {
                uploadStatus.textContent = "";
                uploadStatus.style.display = "none";
            }, 3000);
        } else {
            uploadStatus.textContent = "Error: " + data.message;
            uploadStatus.style.color = "red";
        }
    })
    .catch(error => {
        uploadStatus.textContent = "Error: " .concat(error.message);
        uploadStatus.style.color = "red";
        console.error("Error:", error);
    });
}
});