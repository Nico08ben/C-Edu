document.addEventListener('DOMContentLoaded', () => {
    const userTable = document.getElementById('userTable');
    const newUserButton = document.getElementById('newUser');

    userTable.addEventListener('click', (e) => {
        if (e.target.classList.contains('delete')) {
            e.target.closest('tr').remove();
        } else if (e.target.classList.contains('edit')) {
            const row = e.target.closest('tr');
            const cells = row.querySelectorAll('td');
            const name = prompt('Editar nombre:', cells[1].innerText);
            const subject = prompt('Editar materia:', cells[2].innerText);
            const email = prompt('Editar correo:', cells[3].innerText);
            if (name && subject && email) {
                cells[1].innerText = name;
                cells[2].innerText = subject;
                cells[3].innerText = email;
            }
        }
    });

    newUserButton.addEventListener('click', () => {
        const name = prompt('Nombre completo:');
        const subject = prompt('Materia:');
        const email = prompt('Correo:');
        if (name && subject && email) {
            userTable.innerHTML += `
                <tr>
                    <td><img src="default_avatar.png" alt="Perfil"></td>
                    <td>${name}</td>
                    <td>${subject}</td>
                    <td>${email}</td>
                    <td>
                        <button class="edit">✏️</button>
                        <button class="delete">🗑️</button>
                    </td>
                </tr>`;
        }
    });
});
