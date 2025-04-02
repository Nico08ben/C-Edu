document.addEventListener("DOMContentLoaded", () => {
    // Elementos del DOM
    const userTable = document.querySelector("table tbody");
    const newUserButton = document.getElementById("newUser");
    const saveButton = document.getElementById("save");
    const userModal = document.getElementById("userModal");
    const editUserModal = document.getElementById("editUserModal");
    const closeButtons = document.querySelectorAll(".close-button");
    const newUserForm = document.getElementById("newUserForm");
    const editUserForm = document.getElementById("editUserForm");

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

                // Elegir avatar aleatorio
                const avatarNum = Math.floor(Math.random() * 4) + 1;

                // Agregar a la tabla solo si el usuario fue insertado correctamente
                const newRow = document.createElement("tr");
                newRow.dataset.userId = userId;
                newRow.dataset.telefono = formData.get("telefono_usuario") || "";
                newRow.dataset.institucion = formData.get("id_institucion");
                newRow.dataset.rol = formData.get("id_rol");
                newRow.dataset.materia = formData.get("materia"); // Guardar materia en atributo data

                newRow.innerHTML = `
                    <td><img src="../../assets/avatar${avatarNum}.jpg" alt="Avatar"></td>
                    <td>${formData.get("nombre_usuario")}</td>
                    <td>${formData.get("materia")}</td>
                    <td>${formData.get("email_usuario")}</td>
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
                        row.querySelector("td:nth-child(3)").textContent =
                            formData.get("materia");
                        row.querySelector("td:nth-child(4)").textContent =
                            formData.get("email_usuario");

                        // Actualizar los datos en los atributos data-*
                        row.dataset.telefono = formData.get("telefono_usuario") || "";
                        row.dataset.institucion = formData.get("id_institucion");
                        row.dataset.rol = formData.get("id_rol");
                        row.dataset.materia = formData.get("materia");
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
                // Asumimos que el ID debe estar disponible en algún lugar de la fila
                // En este caso, vamos a añadir un método para obtenerlo de PHP directamente
                console.warn("No se encontró ID de usuario para esta fila");
                
                // Opción alternativa: usar el índice de la fila + 1 como ID temporal
                const rows = Array.from(userTable.querySelectorAll("tr"));
                userId = rows.indexOf(row) + 1;
            }
        }
        
        const name = row.querySelector("td:nth-child(2)").textContent;
        const materia = row.querySelector("td:nth-child(3)").textContent;
        const email = row.querySelector("td:nth-child(4)").textContent;
        const telefono = row.dataset.telefono || "";
        const institucion = row.dataset.institucion || "1";
        const rol = row.dataset.rol || "1";

        // Llenar el formulario de edición con los datos actuales
        document.getElementById("edit_id_usuario").value = userId;
        document.getElementById("edit_nombre_usuario").value = name;
        document.getElementById("edit_email_usuario").value = email;
        document.getElementById("edit_telefono_usuario").value = telefono;

        // Seleccionar la materia correcta
        const materiaSelect = document.getElementById("edit_materia");
        for (let i = 0; i < materiaSelect.options.length; i++) {
            if (materiaSelect.options[i].value === materia) {
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

    // Botón de guardar
    saveButton.addEventListener("click", function () {
        alert("Cambios guardados correctamente");
    });

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