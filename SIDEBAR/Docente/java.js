// Seleccionar elementos del DOM
const body = document.querySelector("html");
const sidebar = document.querySelector(".sidebar");
const toggle = document.querySelector(".sidebar .toggle");
const searchBtn = document.querySelector(".sidebar .search-box");
const modeSwitch = document.querySelector(".sidebar .toggle-switch");
const modeText = document.querySelector(".sidebar .mode-text");

// Función para aplicar el tema actual y actualizar UI
function applyCurrentTheme() {
    const savedTheme = localStorage.getItem('themeMode');

    if (body && modeText) {
        if (savedTheme === 'dark') {
            body.classList.add('dark');
            document.documentElement.classList.add('dark');
            modeText.innerText = "Light Mode";
        } else {
            body.classList.remove('dark');
            document.documentElement.classList.remove('dark');
            modeText.innerText = "Dark Mode";
        }
    }
}

// Función para aplicar el estado del sidebar
function applySidebarState() {
    if (sidebar) {
        const savedSidebarState = localStorage.getItem('sidebarState');
        if (savedSidebarState === 'close') {
            sidebar.classList.add('close');
        } else {
            sidebar.classList.remove('close');
        }
    }
}

// Aplicar tema y estado del sidebar cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', () => {
    applyCurrentTheme();
    applySidebarState();
});

// Toggle para el sidebar
if (toggle && sidebar) {
    toggle.addEventListener("click", () => {
        sidebar.classList.toggle("close");
        if (sidebar.classList.contains("close")) {
            localStorage.setItem("sidebarState", "close");
        } else {
            localStorage.setItem("sidebarState", "open");
        }
    });
}

// Botón de búsqueda para abrir el sidebar
if (searchBtn && sidebar) {
    searchBtn.addEventListener("click", () => {
        sidebar.classList.remove("close");
        localStorage.setItem("sidebarState", "open");
    });
}

// Switch para el modo oscuro/claro
if (modeSwitch && body && modeText) {
    modeSwitch.addEventListener("click", () => {
        body.classList.toggle("dark");
        document.documentElement.classList.toggle('dark'); // Sincronizar también con <html>

        if (body.classList.contains("dark")) {
            modeText.innerText = "Light Mode";
            localStorage.setItem("themeMode", "dark");
        } else {
            modeText.innerText = "Dark Mode";
            localStorage.setItem("themeMode", "light");
        }
    });
}