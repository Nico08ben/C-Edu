document.addEventListener("DOMContentLoaded", function () {
    const body = document.body;
    const toggleSwitch = document.querySelector(".toggle-switch");
    const modeText = document.querySelector(".mode-text");

    toggleSwitch.addEventListener("click", function () {
        let modo = body.classList.contains("dark") ? "claro" : "oscuro";

        body.classList.toggle("dark");
        modeText.textContent = modo === "oscuro" ? "Modo Claro" : "Modo Oscuro";

        // Enviar la preferencia a la base de datos
        fetch("guardar_modo.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: "modo=" + modo
        })
        .then(response => response.text())
        .then(data => console.log(data));
    });
});
