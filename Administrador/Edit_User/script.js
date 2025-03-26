document.addEventListener('DOMContentLoaded', () => {
    const userTable = document.getElementById('userTable');
    const newUserButton = document.getElementById('newUser');
    const userModal = document.getElementById('userModal');
    const closeButton = document.querySelector('.close-button');
    const newUserForm = document.getElementById('newUserForm');

    // Modal functionality
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

    // Event listeners for modal
    newUserButton.addEventListener('click', openModal);
    closeButton.addEventListener('click', closeModal);

    // Close modal if clicked outside of it
    userModal.addEventListener('click', (e) => {
        if (e.target === userModal) {
            closeModal();
        }
    });

    // Prevent modal content from closing when clicked inside
    document.querySelector('.modal-content').addEventListener('click', (e) => {
        e.stopPropagation();
    });

    // Form submission handler
    newUserForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Collect form data
        const formData = {
            nombre_usuario: document.getElementById('nombre_usuario').value,
            email_usuario: document.getElementById('email_usuario').value,
            contraseña_usuario: document.getElementById('contraseña_usuario').value,
            telefono_usuario: document.getElementById('telefono_usuario').value,
            id_institucion: document.getElementById('id_institucion').value,
            id_rol: document.getElementById('id_rol').value
        };

        // Add new row to table
        userTable.innerHTML += `
            <tr>
                <td><img src="perfil.png" alt="Perfil"></td>
                <td>${formData.nombre_usuario}</td>
                <td>${formData.email_usuario}</td>
                <td>${formData.telefono_usuario}</td>
                <td>Institución de prueba</td>
                <td>Usuario</td>
                <td class="action-buttons">
                    <button class="edit">✏️</button>
                    <button class="delete">🗑️</button>
                </td>
            </tr>
        `;

        // Reset form and close modal
        newUserForm.reset();
        closeModal();
    });

    // Existing edit and delete functionality
    userTable.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete')) {
            e.target.closest('tr').remove();
        } else if (e.target.classList.contains('edit')) {
            const row = e.target.closest('tr');
            const cells = row.querySelectorAll('td');
            
            // Update form fields with current row data
            document.getElementById('nombre_usuario').value = cells[1].innerText;
            document.getElementById('email_usuario').value = cells[2].innerText;
            document.getElementById('telefono_usuario').value = cells[3].innerText;
            
            // Open modal in edit mode
            openModal();
        }
    });
});