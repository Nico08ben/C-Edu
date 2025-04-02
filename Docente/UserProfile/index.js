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
        
        // Mostrar mensaje de carga
        uploadStatus.textContent = "Subiendo imagen...";
        uploadStatus.style.color = "blue";
        uploadStatus.style.display = "block"; // Asegurar que sea visible
        
        fetch("upload_image.php", {
            method: "POST",
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                uploadStatus.textContent = "¡Imagen actualizada!";
                uploadStatus.style.color = "green";
                
                // Ocultar el mensaje después de 3 segundos
                setTimeout(() => {
                    uploadStatus.textContent = "";
                }, 3000);
            } else {
                uploadStatus.textContent = "Error: " + data.message;
                uploadStatus.style.color = "red";
            }
        })
        .catch(error => {
            uploadStatus.textContent = "Error de conexión: " + error.message;
            uploadStatus.style.color = "red";
            console.error("Error:", error);
        });
    }
});