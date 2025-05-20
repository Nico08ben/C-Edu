// java.js
// Seleccionar elementos del DOM
const body = document.querySelector("body");
const sidebar = document.querySelector(".sidebar");
const toggle = document.querySelector(".sidebar .toggle");
const searchBtn = document.querySelector(".sidebar .search-box");
const modeSwitch = document.querySelector(".sidebar .toggle-switch");
const modeText = document.querySelector(".sidebar .mode-text");

// !!! IMPORTANTE: Este script asume que una variable global `currentUserId` existe
// y contiene un identificador único para el usuario logueado.
// Debe ser definida por tu lógica del servidor (PHP) ANTES de que este script se cargue.
// Ejemplo en PHP: <script> const currentUserId = "<?php echo $userId; ?>"; </script>

/**
 * Genera una clave para localStorage que es específica del usuario.
 * @param {string} baseKey - La clave base (ej. 'themeMode', 'sidebarState').
 * @returns {string} - La clave específica del usuario (ej. 'themeMode_usuario123').
 */
function getUserSpecificKey(baseKey) {
    if (typeof currentUserId !== 'undefined' && currentUserId && currentUserId !== 'guest') {
        return `${baseKey}_${currentUserId}`;
    }
    // Fallback para invitados o si currentUserId no está bien definido.
    // Los invitados o usuarios sin ID compartirán la configuración "_default".
    // console.warn(`currentUserId no está definido o es 'guest'. Usando clave por defecto para ${baseKey}.`);
    return `${baseKey}_default`;
}

// Función para aplicar el tema actual y actualizar UI
function applyCurrentTheme() {
    const themeKey = getUserSpecificKey('themeMode'); // Clave específica del usuario
    const savedTheme = localStorage.getItem(themeKey);

    if (body && modeText) { // Asegurarse que los elementos existen
        if (savedTheme === 'dark') {
            body.classList.add('dark');
            document.documentElement.classList.add('dark'); // Aplicar también al elemento <html>
            modeText.innerText = "Light Mode";
        } else {
            body.classList.remove('dark');
            document.documentElement.classList.remove('dark'); // Remover también del elemento <html>
            modeText.innerText = "Dark Mode";
        }
    }
}

// Función para aplicar el estado del sidebar
function applySidebarState() {
    if (sidebar) { // Asegurarse que el sidebar existe
        const sidebarKey = getUserSpecificKey('sidebarState'); // Clave específica del usuario
        const savedSidebarState = localStorage.getItem(sidebarKey);
        if (savedSidebarState === 'close') {
            sidebar.classList.add('close');
        } else {
            sidebar.classList.remove('close');
        }
    }
}

// Aplicar tema y estado del sidebar cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', () => {
    if (typeof currentUserId === 'undefined') {
        console.error("ADVERTENCIA: La variable 'currentUserId' no está definida. Las configuraciones no podrán ser específicas del usuario y podrían usar valores por defecto compartidos.");
    }
    applyCurrentTheme();
    applySidebarState();
});

// Toggle para el sidebar
if (toggle && sidebar) {
    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
        const sidebarKey = getUserSpecificKey('sidebarState'); // Clave específica del usuario
        if (sidebar.classList.contains("close")) {
            localStorage.setItem(sidebarKey, "close");
        } else {
            localStorage.setItem(sidebarKey, "open");
        }
    });
}

// Botón de búsqueda para abrir el sidebar (si aplica guardar estado)
if (searchBtn && sidebar) {
    searchBtn.addEventListener("click", () => {
        sidebar.classList.remove("close");
        const sidebarKey = getUserSpecificKey('sidebarState'); // Clave específica del usuario
        localStorage.setItem(sidebarKey, "open"); // Guardar estado del sidebar
    });
}

// Switch para el modo oscuro/claro
if (modeSwitch && body && modeText) {
    modeSwitch.addEventListener("click", () => {
        body.classList.toggle("dark");
        document.documentElement.classList.toggle('dark'); // Sincronizar también con <html>
        const themeKey = getUserSpecificKey('themeMode'); // Clave específica del usuario

        if (body.classList.contains("dark")) {
            modeText.innerText = "Light Mode";
            localStorage.setItem(themeKey, "dark");
        } else {
            modeText.innerText = "Dark Mode";
            localStorage.setItem(themeKey, "light");
        }
    });
}