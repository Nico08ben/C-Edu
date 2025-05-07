document.addEventListener("DOMContentLoaded", () => {
    // Elementos del DOM
    const userTable = document.querySelector("table tbody");
    const newUserButton = document.getElementById("newUser");
    const saveButton = document.getElementById("save"); // Considera la funcionalidad de este botón
    const userModal = document.getElementById("userModal");
    const editUserModal = document.getElementById("editUserModal");
    const closeButtons = document.querySelectorAll(".close-button");
    const newUserForm = document.getElementById("newUserForm");
    const editUserForm = document.getElementById("editUserForm");
    const changePasswordModal = document.getElementById("changePasswordModal");
    const changePasswordForm = document.getElementById("changePasswordForm");
    const openPasswordButton = document.getElementById("openPasswordChange");

    // Ruta base para los archivos PHP manejadores (endpoints AJAX)
    // Asumiendo que admin_user_management.php está en public/ y los handlers en src/modules/user_management/
    const basePath = "../src/modules/user_management/";

    // Cargar materias al inicio
    if (typeof cargarMaterias === "function") { // Asegurarse que la función exista si se llama globalmente
        cargarMaterias();
    }


    // Función para cargar materias desde la base de datos
    async function cargarMaterias() {
        try {
            // Antes: "obtener_materias.php"
            // Ahora: "get_subjects_ajax.php"
            const response = await fetch(`${basePath}get_subjects_ajax.php`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const materias = await response.json();

            const materiaSelects = document.querySelectorAll('#id_materia, #edit_id_materia');

            materiaSelects.forEach(select => {
                const defaultOptionValue = select.options.length > 0 ? select.options[0].value : "";
                const defaultOptionText = select.options.length > 0 ? select.options[0].textContent : "Seleccionar materia";
                
                select.innerHTML = ''; // Limpiar opciones existentes

                // Re-agregar la opción por defecto si es necesario o deseado
                const firstOption = document.createElement('option');
                firstOption.value = defaultOptionValue; // Usualmente "" para "Seleccionar"
                firstOption.textContent = defaultOptionText;
                if (defaultOptionValue === "") firstOption.disabled = true; // Opcional: deshabilitar si es un placeholder
                select.appendChild(firstOption);


                materias.forEach(materia => {
                    const option = document.createElement('option');
                    option.value = materia.id_materia;
                    option.textContent = materia.nombre_materia;
                    select.appendChild(option);
                });
            });
        } catch (error) {
            console.error("Error al cargar materias:", error);
            // Considera mostrar un mensaje al usuario aquí
        }
    }

    // Funciones para los modales (sin cambios)
    function openModal(modal) {
        if (!modal) return;
        modal.style.display = "flex";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10);
    }

    function closeModal(modal) {
        if (!modal) return;
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
        }, 300);
    }

    // Event listeners para abrir/cerrar el modal de creación
    if (newUserButton) {
        newUserButton.addEventListener("click", () => openModal(userModal));
    }

    closeButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const modal = this.closest(".modal");
            closeModal(modal);
        });
    });

    document.querySelectorAll(".modal").forEach((modal) => {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    });

    // Manejar envío del formulario de nuevo usuario
    if (newUserForm) {
        newUserForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(newUserForm);
            const materiaSelect = document.getElementById("id_materia");
            const materiaNombre = materiaSelect.options[materiaSelect.selectedIndex].text;

            try {
                // Antes: "procesar_usuario.php"
                // Ahora: "create_user_handler.php"
                const response = await fetch(`${basePath}create_user_handler.php`, {
                    method: "POST",
                    body: formData,
                });

                const resultText = await response.text(); // Obtener siempre el texto para depurar
                
                // Aquí puedes procesar resultText si esperas JSON o un formato específico
                // Por ahora, asumimos que el PHP envía un mensaje de texto que se muestra en alert
                alert(resultText); 

                if (response.ok) { // O verifica una condición más específica en resultText si es necesario
                    // Actualizar la tabla (el código existente parece razonable, ajustar según la respuesta real del servidor)
                    // ... (tu lógica para agregar fila a la tabla) ...
                    // Ejemplo simplificado: Recargar la página o la lista de usuarios para ver los cambios
                    // location.reload(); // Opción simple pero podría no ser la mejor UX

                    newUserForm.reset();
                    closeModal(userModal);
                    // Considera una función para recargar la tabla de usuarios o añadir la fila dinámicamente
                    // setupActionButtons(); // Si añades filas dinámicamente, necesitas reasignar listeners
                }
            } catch (error) {
                console.error("Error al enviar los datos para crear usuario:", error);
                alert("Error de conexión al crear el usuario.");
            }
        });
    }

    // Manejar envío del formulario de edición de usuario
    if (editUserForm) {
        editUserForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(editUserForm);
            // const userId = formData.get("id_usuario"); // Ya está en formData
            // const materiaSelect = document.getElementById("edit_id_materia");
            // const materiaNombre = materiaSelect.options[materiaSelect.selectedIndex].text;

            try {
                // Antes: "actualizar_usuario.php"
                // Ahora: "update_user_handler.php"
                const response = await fetch(`${basePath}update_user_handler.php`, {
                    method: "POST",
                    body: formData,
                });

                const resultText = await response.text();
                alert(resultText);

                if (response.ok) {
                    // Actualizar la fila en la tabla (el código existente parece razonable)
                    // ... (tu lógica para actualizar fila en la tabla) ...
                    // Ejemplo simplificado: Recargar la página o la lista de usuarios
                    // location.reload();
                    closeModal(editUserModal);
                }
            } catch (error) {
                console.error("Error al actualizar los datos del usuario:", error);
                alert("Error de conexión al actualizar el usuario.");
            }
        });
    }

    // Listener para abrir el modal de cambio de contraseña
    if (openPasswordButton) {
        openPasswordButton.addEventListener("click", () => {
            const userId = document.getElementById("edit_id_usuario").value;
            if (document.getElementById("password_user_id")) {
                 document.getElementById("password_user_id").value = userId;
            }
            closeModal(editUserModal);
            openModal(changePasswordModal);
        });
    }
    
    // Listener para cerrar el modal de contraseña (asegúrate que el selector es correcto)
    const closePasswordButton = document.querySelector("#changePasswordModal .close-button, #changePasswordModal .close-password");
    if (closePasswordButton) {
        closePasswordButton.addEventListener("click", () => {
            closeModal(changePasswordModal);
        });
    }


    // Manejar envío del formulario de cambio de contraseña
    if (changePasswordForm) {
        changePasswordForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(changePasswordForm);
            const newPassword = formData.get("new_password");
            const confirmPassword = formData.get("confirm_password");

            if (newPassword !== confirmPassword) {
                alert("Las contraseñas no coinciden.");
                return;
            }

            try {
                // Antes: "cambiar_password.php"
                // Ahora: "change_password_handler.php"
                const response = await fetch(`${basePath}change_password_handler.php`, {
                    method: "POST",
                    body: formData
                });

                const resultText = await response.text();
                alert(resultText);

                if (response.ok) {
                    closeModal(changePasswordModal);
                    if (changePasswordForm) changePasswordForm.reset();
                }
            } catch (error) {
                console.error("Error al cambiar contraseña:", error);
                alert("Error de conexión al cambiar la contraseña.");
            }
        });
    }

    // Función para configurar los listeners de los botones de acción (sin cambios en la lógica interna)
    function setupActionButtons() {
        document.querySelectorAll(".edit").forEach((button) => {
            button.removeEventListener("click", handleEditButton); // Prevenir duplicados si se llama múltiples veces
            button.addEventListener("click", handleEditButton);
        });

        document.querySelectorAll(".delete").forEach((button) => {
            button.removeEventListener("click", handleDeleteButton); // Prevenir duplicados
            button.addEventListener("click", handleDeleteButton);
        });
    }
    
    // Wrapper para que los handlers de evento puedan ser removidos y re-agregados
    function handleEditButton(event) {
        const button = event.currentTarget; // 'this' puede ser problemático si la función no es llamada directamente por el listener
        const row = button.closest("tr");
        if (!row) return;

        let userId = row.dataset.userId || row.getAttribute("data-id-usuario");
        // ... (resto de tu lógica de handleEditButton, asegurar que los IDs de los elementos del form son correctos) ...
        // Ejemplo de cómo poblar el formulario (asegúrate que los IDs de los inputs son correctos):
        const name = row.cells[1].textContent; // Asumiendo que el nombre está en la segunda celda
        const email = row.cells[3].textContent; // Asumiendo que el email está en la cuarta celda

        if(document.getElementById("edit_id_usuario")) document.getElementById("edit_id_usuario").value = userId;
        if(document.getElementById("edit_nombre_usuario")) document.getElementById("edit_nombre_usuario").value = name;
        if(document.getElementById("edit_email_usuario")) document.getElementById("edit_email_usuario").value = email;
        // Poblar otros campos como teléfono, institución, rol, materia
        if(document.getElementById("edit_telefono_usuario") && row.dataset.telefono) document.getElementById("edit_telefono_usuario").value = row.dataset.telefono;
        if(document.getElementById("edit_id_institucion") && row.dataset.institucion) document.getElementById("edit_id_institucion").value = row.dataset.institucion;
        if(document.getElementById("edit_id_rol") && row.dataset.rol) document.getElementById("edit_id_rol").value = row.dataset.rol;
        if(document.getElementById("edit_id_materia") && row.dataset.materia) document.getElementById("edit_id_materia").value = row.dataset.materia;
        
        openModal(editUserModal);
    }

    function handleDeleteButton(event) {
        const button = event.currentTarget;
        const row = button.closest("tr");
        if (!row) return;

        const userName = row.cells[1].textContent; // Asumiendo que el nombre está en la segunda celda
        let userId = row.dataset.userId || row.getAttribute("data-id-usuario");

        if (confirm(`¿Estás seguro de que deseas eliminar a ${userName}? (ID: ${userId})`)) {
            if (userId) {
                const formData = new FormData();
                formData.append('id_usuario', userId);
                // Considera añadir el token CSRF si tus handlers lo requieren para POST vía AJAX
                // formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);


                // Antes: "eliminar_usuario.php"
                // Ahora: "delete_user_handler.php"
                fetch(`${basePath}delete_user_handler.php`, {
                    method: "POST",
                    body: formData, // Enviar como FormData para que $_POST funcione bien en PHP
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => { throw new Error(text || `Error del servidor: ${response.status}`) });
                    }
                    return response.text();
                })
                .then(result => {
                    alert(result);
                    // Si el PHP confirma la eliminación (ej. con un mensaje específico)
                    if (result.toLowerCase().includes("eliminado correctamente")) {
                        row.remove();
                    }
                })
                .catch(error => {
                    console.error("Error al eliminar usuario:", error);
                    alert(`Error al eliminar: ${error.message}`);
                });
            } else {
                 console.warn("No se pudo obtener el ID del usuario para eliminar.");
                 alert("No se pudo eliminar el usuario: ID no encontrado.");
            }
        }
    }

    // Eliminar el listener de click global y usar la asignación directa en setupActionButtons
    // document.addEventListener("click", function (e) { ... }); // Esta sección puede ser eliminada si setupActionButtons se llama correctamente

    // Configurar los event listeners iniciales para botones ya existentes en el DOM
    setupActionButtons();

    // Botón de guardar (si tiene una funcionalidad específica, impleméntala)
    if (saveButton) {
        saveButton.addEventListener("click", function () {
            // Esta alerta es genérica. Define qué debe hacer "Guardar".
            // Podría ser, por ejemplo, disparar el submit del formulario de edición si estuviera fuera.
            // O si es un guardado general de configuraciones de la página.
            alert("Funcionalidad 'Guardar' no completamente definida.");
        });
    }

    // Modificación de la carga inicial de usuarios desde PHP (sin cambios)
    document.querySelectorAll("table tbody tr").forEach((row) => {
        const userIdFromAttribute = row.getAttribute("data-id-usuario");
        if (userIdFromAttribute && !row.dataset.userId) {
            row.dataset.userId = userIdFromAttribute;
        }
    });
});