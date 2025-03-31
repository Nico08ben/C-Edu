document.addEventListener('DOMContentLoaded', () => {
    // Elementos del DOM
    const userTable = document.querySelector('table tbody');
    const newUserButton = document.getElementById('newUser');
    const saveButton = document.getElementById('save');
    const userModal = document.getElementById('userModal');
    const closeButton = document.querySelector('.close-button');
    const newUserForm = document.getElementById('newUserForm');

    // Funciones para el modal
    function openModal() {
        userModal.style.display = 'flex';
        setTimeout(() => {
            userModal.classList.add('show');
        }, 10);
    }

    function closeModal() {
        userModal.classList.remove('show');
        setTimeout(() => {
            userModal.style.display = 'none';
        }, 300);
    }

    // Event listeners para abrir/cerrar el modal
    newUserButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    userModal.addEventListener('click', (e) => {
        if (e.target === userModal) {
            closeModal();
        }
    });

    // Manejar envío del formulario
    newUserForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(newUserForm);

        try {
            const response = await fetch('procesar_usuario.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.text();
            alert(result); // Muestra mensaje de éxito o error

            if (response.ok) {
                // Elegir avatar aleatorio
                const avatarNum = Math.floor(Math.random() * 4) + 1;
                
                // Agregar a la tabla solo si el usuario fue insertado correctamente
                userTable.innerHTML += `
                    <tr>
                        <td><img src="../../assets/avatar${avatarNum}.jpg" alt="Avatar"></td>
                        <td>${formData.get("nombre_usuario")}</td>
                        <td>${formData.get("materia")}</td>
                        <td>${formData.get("email_usuario")}</td>
                        <td class="action-buttons">
                            <button class="edit"><i class="fas fa-edit"></i></button>
                            <button class="delete"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;

                newUserForm.reset();
                closeModal();
                
                // Actualizar los event listeners para los nuevos botones
                setupActionButtons();
            }
        } catch (error) {
            console.error("Error al enviar los datos:", error);
        }
    });

    // Función para configurar los listeners de los botones de acción
    function setupActionButtons() {
        // Configurar botones de edición
        document.querySelectorAll('.edit').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const name = row.querySelector('td:nth-child(2)').textContent;
                const materia = row.querySelector('td:nth-child(3)').textContent;
                const email = row.querySelector('td:nth-child(4)').textContent;
                
                // Llenar el formulario con los datos actuales
                document.getElementById('nombre_usuario').value = name;
                document.getElementById('email_usuario').value = email;
                
                // Seleccionar la materia correcta
                const materiaSelect = document.getElementById('materia');
                for(let i = 0; i < materiaSelect.options.length; i++) {
                    if(materiaSelect.options[i].value === materia) {
                        materiaSelect.selectedIndex = i;
                        break;
                    }
                }
                
                openModal();
            });
        });
        
        // Configurar botones de eliminación
        document.querySelectorAll('.delete').forEach(button => {
            button.addEventListener('click', function() {
                const row = this.closest('tr');
                const userName = row.querySelector('td:nth-child(2)').textContent;
                
                if (confirm('¿Estás seguro de que deseas eliminar a ' + userName + '?')) {
                    row.remove();
                    alert('Usuario eliminado correctamente');
                }
            });
        });
    }

    // Configurar los event listeners iniciales
    setupActionButtons();
    
    // Botón de guardar
    saveButton.addEventListener('click', function() {
        alert('Cambios guardados correctamente');
    });
});