document.addEventListener('DOMContentLoaded', () => {
    const userTable = document.getElementById('userTable');
    const newUserButton = document.getElementById('newUser');
    const userModal = document.getElementById('userModal');
    const closeButton = document.querySelector('.close-button');
    const newUserForm = document.getElementById('newUserForm');

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

    newUserButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);
    userModal.addEventListener('click', (e) => {
        if (e.target === userModal) {
            closeModal();
        }
    });

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
                // Agregar a la tabla solo si el usuario fue insertado correctamente
                userTable.innerHTML += `
                    <tr>
                        <td><img src="perfil.png" alt="Perfil"></td>
                        <td>${formData.get("nombre_usuario")}</td>
                        <td>${formData.get("email_usuario")}</td>
                        <td>${formData.get("telefono_usuario")}</td>
                        <td>${formData.get("id_institucion") || 'N/A'}</td>
                        <td>${formData.get("id_rol") || 'Usuario'}</td>
                        <td class="action-buttons">
                            <button class="edit">✏️</button>
                            <button class="delete">🗑️</button>
                        </td>
                    </tr>
                `;

                newUserForm.reset();
                closeModal();
            }
        } catch (error) {
            console.error("Error al enviar los datos:", error);
        }
    });

    userTable.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete')) {
            e.target.closest('tr').remove();
        } else if (e.target.classList.contains('edit')) {
            const row = e.target.closest('tr');
            const cells = row.querySelectorAll('td');
            
            document.getElementById('nombre_usuario').value = cells[1].innerText;
            document.getElementById('email_usuario').value = cells[2].innerText;
            document.getElementById('telefono_usuario').value = cells[3].innerText;
            
            openModal();
        }
    });
});
