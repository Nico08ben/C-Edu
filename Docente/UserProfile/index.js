document.addEventListener("DOMContentLoaded", function () {
    const editProfileText = document.getElementById("edit-profile");
    const helpText = document.getElementById("help");
    const profileContainer = document.querySelector(".profile-container");
    const ayuda = document.querySelector(".ayuda");
    const editImageButton = document.querySelector(".edit-btn");
    const profileImage = document.querySelector(".user-avatar");
    ayuda.style.display = "none";

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

    // Editar Imagen de Perfil
    editImageButton.addEventListener("click", function () {
        const newImageUrl = prompt("Ingresa la URL de tu nueva imagen:");
        if (newImageUrl) {
            profileImage.style.backgroundImage = `url(${newImageUrl})`;
        }
    });
});
