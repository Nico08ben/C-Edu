document.addEventListener("DOMContentLoaded", () => {
    // Elementos del DOM
    const userTable = document.querySelector("table tbody");
    const newUserButton = document.getElementById("newUser");
    const userModal = document.getElementById("userModal");
    const editUserModal = document.getElementById("editUserModal");
    const closeButtons = document.querySelectorAll(".close-button");
    const newUserForm = document.getElementById("newUserForm");
    const editUserForm = document.getElementById("editUserForm");
    const changePasswordModal = document.getElementById("changePasswordModal");
    const changePasswordForm = document.getElementById("changePasswordForm");
    const openPasswordButton = document.getElementById("openPasswordChange");

    // Cargar materias al inicio
    cargarMaterias();

    // Función para cargar materias desde la base de datos
    async function cargarMaterias() {
        try {
            const response = await fetch("obtener_materias.php");
            const materias = await response.json();

            // Llenar los selectores de materia en ambos formularios
            const materiaSelects = document.querySelectorAll('#id_materia, #edit_id_materia');

            materiaSelects.forEach(select => {
                // Limpiar opciones existentes excepto la primera (si hay una opción predeterminada)
                const defaultOption = select.options[0];
                select.innerHTML = '';
                if (defaultOption) {
                    select.appendChild(defaultOption);
                }

                // Agregar las materias como opciones
                materias.forEach(materia => {
                    const option = document.createElement('option');
                    option.value = materia.id_materia;
                    option.textContent = materia.nombre_materia;
                    select.appendChild(option);
                });
            });
        } catch (error) {
            console.error("Error al cargar materias:", error);
        }
    }

    // Funciones para los modales
    function openModal(modal) {
        modal.style.display = "flex";
        setTimeout(() => {
            modal.classList.add("show");
        }, 10);
    }

    function closeModal(modal) {
        modal.classList.remove("show");
        setTimeout(() => {
            modal.style.display = "none";
        }, 300);
    }

    // Event listeners para abrir/cerrar el modal de creación
    newUserButton.addEventListener("click", () => openModal(userModal));

    // Cerrar cualquier modal al hacer clic en sus botones de cierre
    closeButtons.forEach((button) => {
        button.addEventListener("click", function () {
            const modal = this.closest(".modal");
            closeModal(modal);
        });
    });

    // Cerrar cualquier modal al hacer clic fuera del contenido
    document.querySelectorAll(".modal").forEach((modal) => {
        modal.addEventListener("click", (e) => {
            if (e.target === modal) {
                closeModal(modal);
            }
        });
    });

    // Manejar envío del formulario de nuevo usuario
    newUserForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(newUserForm);

        // Obtener el texto de la materia seleccionada
        const materiaSelect = document.getElementById("id_materia");
        const materiaNombre = materiaSelect.options[materiaSelect.selectedIndex].text;

        const rolSelect = document.getElementById("id_rol"); // El <select> para el rol en newUserForm
        const rolNombre = rolSelect.options[rolSelect.selectedIndex].text; // El texto visible, ej: "Administrador"


        try {
            const response = await fetch("procesar_usuario.php", {
                method: "POST",
                body: formData,
            });

            const result = await response.text();
            alert(result); // Muestra mensaje de éxito o error

            if (response.ok) {
                // Intentar extraer el ID del usuario recién creado (si el servidor lo devuelve)
                let userId = "";
                if (result.includes("id:")) {
                    const match = result.match(/id:\s*(\d+)/);
                    if (match && match[1]) {
                        userId = match[1];
                    }
                }


                // Agregar a la tabla solo si el usuario fue insertado correctamente
                const newRow = document.createElement("tr");
                newRow.dataset.userId = userId;
                newRow.dataset.telefono = formData.get("telefono_usuario") || "";
                newRow.dataset.institucion = formData.get("id_institucion");
                newRow.dataset.rol = formData.get("id_rol");
                newRow.dataset.materia = formData.get("id_materia"); // ID de la materia

                newRow.innerHTML = `
                    <td><img src="/C-edu/uploads/profile_pictures/default-avatar.png" alt="Avatar"></td>
                    <td>${formData.get("nombre_usuario")}</td>
                    <td>${materiaNombre}</td>
                    <td>${formData.get("email_usuario")}</td>
                    <td>${rolNombre}</td>
                    <td class="action-buttons">
                        <button class="edit"><i class="fas fa-edit"></i></button>
                        <button class="delete"><i class="fas fa-trash"></i></button>
                    </td>
                `;

                userTable.appendChild(newRow);
                newUserForm.reset();
                closeModal(userModal);

                // Actualizar los event listeners para los nuevos botones
                setupActionButtons();
            }
        } catch (error) {
            console.error("Error al enviar los datos:", error);
        }
    });

    // Manejar envío del formulario de edición de usuario
    editUserForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(editUserForm);
        const userId = formData.get("id_usuario");

        // Obtener el texto de la materia seleccionada
        const materiaSelect = document.getElementById("edit_id_materia");
        const materiaNombre = materiaSelect.options[materiaSelect.selectedIndex].text;

        try {
            const response = await fetch("actualizar_usuario.php", {
                method: "POST",
                body: formData,
            });

            const result = await response.text();

            if (response.ok) {
                alert(result); // Muestra mensaje de éxito

                // Buscar la fila correspondiente al usuario editado
                const rows = userTable.querySelectorAll("tr");
                for (let row of rows) {
                    if (row.dataset.userId === userId) {
                        // Actualizar los datos en la tabla
                        row.querySelector("td:nth-child(2)").textContent =
                            formData.get("nombre_usuario");
                        row.querySelector("td:nth-child(3)").textContent = materiaNombre;
                        row.querySelector("td:nth-child(4)").textContent =
                            formData.get("email_usuario");

                        // Actualizar los datos en los atributos data-*
                        row.dataset.telefono = formData.get("telefono_usuario") || "";
                        row.dataset.institucion = formData.get("id_institucion");
                        row.dataset.rol = formData.get("id_rol");
                        row.dataset.materia = formData.get("id_materia");
                        break;
                    }
                }

                closeModal(editUserModal);
            } else {
                alert("Error al actualizar: " + result);
            }
        } catch (error) {
            console.error("Error al actualizar los datos:", error);
            alert("Error de conexión al actualizar el usuario");
        }
    });
    // Nuevo listener para abrir el modal de contraseña
    openPasswordButton.addEventListener("click", () => {
        // Obtener el ID del usuario del formulario de edición
        const userId = document.getElementById("edit_id_usuario").value;
        document.getElementById("password_user_id").value = userId;

        closeModal(editUserModal);  // Cerrar modal de edición
        openModal(changePasswordModal);
    });

    // Listener para cerrar el modal de contraseña
    document.querySelector(".close-password").addEventListener("click", () => {
        closeModal(changePasswordModal);
    });

    // Manejar envío del formulario de cambio de contraseña
    changePasswordForm.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(changePasswordForm);
        const newPassword = formData.get("new_password");
        const confirmPassword = formData.get("confirm_password");

        if (newPassword !== confirmPassword) {
            alert("Las contraseñas no coinciden");
            return;
        }

        try {
            const response = await fetch("cambiar_password.php", {
                method: "POST",
                body: formData
            });

            const result = await response.text();
            alert(result);

            if (response.ok) {
                closeModal(changePasswordModal);
            }
        } catch (error) {
            console.error("Error al cambiar contraseña:", error);
        }
    });
    // Función para configurar los listeners de los botones de acción
    function setupActionButtons() {
        // Configurar botones de edición
        document.querySelectorAll(".edit").forEach((button) => {
            button.addEventListener("click", function () {
                handleEditButton(this);
            });
        });

        // Configurar botones de eliminación
        document.querySelectorAll(".delete").forEach((button) => {
            button.addEventListener("click", function () {
                handleDeleteButton(this);
            });
        });
    }

    // Función para manejar el botón de edición
    function handleEditButton(button) {
        const row = button.closest("tr");

        // Si no hay un ID de usuario en el atributo data, buscar en las celdas para usuarios cargados desde PHP
        let userId = row.dataset.userId;
        if (!userId || userId === "") {
            // Intentar extraer el ID del usuario del atributo data-id-usuario si existe
            userId = row.getAttribute("data-id-usuario");

            // Si aún no hay ID, buscar en el DOM para usuarios generados por PHP
            if (!userId || userId === "") {
                console.warn("No se encontró ID de usuario para esta fila");

                // Opción alternativa: usar el índice de la fila + 1 como ID temporal
                const rows = Array.from(userTable.querySelectorAll("tr"));
                userId = rows.indexOf(row) + 1;
            }
        }

        const name = row.querySelector("td:nth-child(2)").textContent;
        const materiaText = row.querySelector("td:nth-child(3)").textContent;
        const email = row.querySelector("td:nth-child(4)").textContent;
        const telefono = row.dataset.telefono || "";
        const institucion = row.dataset.institucion || "1";
        const rol = row.dataset.rol || "1";
        const materiaId = row.dataset.materia || "1";
        const fechaNacimiento = row.dataset.fechaNacimiento || "";

        // Llenar el formulario de edición con los datos actuales
        document.getElementById("edit_id_usuario").value = userId;
        document.getElementById("edit_nombre_usuario").value = name;
        document.getElementById("edit_email_usuario").value = email;
        document.getElementById("edit_telefono_usuario").value = telefono;
        console.log(fechaNacimiento);

        // Seleccionar la materia correcta
        const materiaSelect = document.getElementById("edit_id_materia");
        for (let i = 0; i < materiaSelect.options.length; i++) {
            if (materiaSelect.options[i].value === materiaId) {
                materiaSelect.selectedIndex = i;
                break;
            }
            // Si no encuentra por ID, intentar por texto
            if (materiaSelect.options[i].textContent === materiaText) {
                materiaSelect.selectedIndex = i;
                break;
            }
        }

        // Seleccionar institución y rol
        document.getElementById("edit_id_institucion").value = institucion;
        document.getElementById("edit_id_rol").value = rol;

        // Abrir el modal de edición
        openModal(editUserModal);
    }

    // Función para manejar el botón de eliminación
    function handleDeleteButton(button) {
        const row = button.closest("tr");
        const userName = row.querySelector("td:nth-child(2)").textContent;

        // Intentar obtener el ID de la misma manera que en handleEditButton
        let userId = row.dataset.userId;
        if (!userId || userId === "") {
            userId = row.getAttribute("data-id-usuario");

            if (!userId || userId === "") {
                const rows = Array.from(userTable.querySelectorAll("tr"));
                userId = rows.indexOf(row) + 1;
            }
        }

        if (confirm("¿Estás seguro de que deseas eliminar a " + userName + "?")) {
            // Si hay un ID de usuario, enviar solicitud para eliminarlo de la base de datos
            if (userId) {
                fetch("eliminar_usuario.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                    body: "id_usuario=" + userId,
                })
                    .then((response) => response.text())
                    .then((result) => {
                        alert(result);
                        if (result.includes("correctamente")) {
                            row.remove();
                        }
                    })
                    .catch((error) => {
                        console.error("Error al eliminar usuario:", error);
                    });
            } else {
                // Si no hay ID (usuario nuevo que aún no está en la BD), simplemente eliminar la fila
                row.remove();
                alert("Usuario eliminado correctamente");
            }
        }
    }

    // Para manejar los botones de edición dinámicamente
    document.addEventListener("click", function (e) {
        // Si se hizo clic en un botón de edición o en el ícono dentro del botón
        if (
            e.target.classList.contains("edit") ||
            (e.target.parentElement &&
                e.target.parentElement.classList.contains("edit"))
        ) {
            const button = e.target.classList.contains("edit")
                ? e.target
                : e.target.parentElement;
            handleEditButton(button);
        }

        // Si se hizo clic en un botón de eliminación o en el ícono dentro del botón
        if (
            e.target.classList.contains("delete") ||
            (e.target.parentElement &&
                e.target.parentElement.classList.contains("delete"))
        ) {
            const button = e.target.classList.contains("delete")
                ? e.target
                : e.target.parentElement;
            handleDeleteButton(button);
        }
    });

    // Configurar los event listeners iniciales
    setupActionButtons();

    // Modificación de la carga inicial de usuarios desde PHP
    document.querySelectorAll("table tbody tr").forEach((row, index) => {
        // Extraer ID de usuario del DOM si está disponible
        const userId = row.getAttribute("data-id-usuario") || (index + 1).toString();

        // Agregar atributo data-user-id a todas las filas existentes
        if (!row.dataset.userId) {
            row.dataset.userId = userId;
        }
    });
});