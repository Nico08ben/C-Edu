// js/notificaciones.js
document.addEventListener("DOMContentLoaded", function () {
    const notificacionesIcono = document.getElementById("notificacionesIcono");
    const notificacionesContador = document.getElementById(
        "notificacionesContador"
    );
    const notificacionesDropdown = document.getElementById(
        "notificacionesDropdown"
    );
    const listaNotificaciones = document.getElementById("listaNotificaciones");
    const marcarTodasLeidasBtn = document.getElementById("marcarTodasLeidasBtn");

    // Obtén el ID_USUARIO_LOGUEADO. Si no está definido globalmente,
    // necesitarás otra forma de obtenerlo (ej. de un data-attribute en el body).
    // const idUsuarioActual = document.body.dataset.idUsuario; // Si lo pones en <body data-id-usuario="X">
    const idUsuarioActual =
        typeof ID_USUARIO_LOGUEADO !== "undefined" ? ID_USUARIO_LOGUEADO : null;

    if (!idUsuarioActual) {
        console.warn(
            "Módulo de notificaciones: ID de usuario no encontrado. Las notificaciones no funcionarán."
        );
        if (notificacionesIcono) notificacionesIcono.style.display = "none"; // Ocultar si no hay usuario
        return;
    }

    const API_URL_OBTENER = "/C-Edu/PHP/api/obtener_notificaciones.php";
    const API_URL_MARCAR_LEIDA = "/C-Edu/PHP/api/marcar_leida.php";
    const API_URL_MARCAR_TODAS = "/C-Edu/PHP/api/marcar_todas_leidas.php";
    const POLLING_INTERVAL = 30000; // 30 segundos

    function formatearFecha(fechaISO) {
        const fecha = new Date(fechaISO);
        return fecha.toLocaleString("es-CO", {
            // Ajusta a tu localidad preferida
            day: "numeric",
            month: "short",
            year: "numeric",
            hour: "numeric",
            minute: "2-digit",
            hour12: true,
        });
    }

    async function cargarNotificaciones() {
        if (!idUsuarioActual) return;
        try {
            const response = await fetch(
                `${API_URL_OBTENER}?id_usuario=${idUsuarioActual}&limite=10`
            ); // Pide las últimas 10
            if (!response.ok) {
                console.error(
                    "Error en la respuesta del servidor al cargar notificaciones:",
                    response.status
                );
                listaNotificaciones.innerHTML =
                    '<li class="sin-notificaciones">Error al cargar.</li>';
                return;
            }
            const data = await response.json();

            actualizarContador(data.total_no_leidas || 0);
            listaNotificaciones.innerHTML = ""; // Limpiar lista

            if (data.notificaciones && data.notificaciones.length === 0) {
                listaNotificaciones.innerHTML =
                    '<li class="sin-notificaciones">No tienes notificaciones.</li>';
            } else if (data.notificaciones) {
                data.notificaciones.forEach((notif) => {
                    const li = document.createElement("li");
                    li.dataset.id = notif.id_notificacion;
                    li.classList.add(
                        notif.estado_notificacion === "no leída" ? "no-leida" : "leida"
                    );

                    let contenidoHTML = `<span class="mensaje">${notif.mensaje || "Notificación sin mensaje."
                        }</span>`;
                    if (notif.tipo_notificacion) {
                        contenidoHTML += `<span class="tipo">Tipo: ${notif.tipo_notificacion.replace(
                            /_/g,
                            " "
                        )}</span>`;
                    }
                    contenidoHTML += `<span class="fecha">${formatearFecha(
                        notif.fecha_notificacion
                    )}</span>`;
                    li.innerHTML = contenidoHTML;

                    li.addEventListener("click", () => manejarClickNotificacion(notif));
                    listaNotificaciones.appendChild(li);
                });
            }
        } catch (error) {
            console.error("Error al cargar notificaciones:", error);
            listaNotificaciones.innerHTML =
                '<li class="sin-notificaciones">Error al cargar. Intenta de nuevo.</li>';
        }
    }

    function actualizarContador(numero) {
        notificacionesContador.textContent = numero;
        notificacionesContador.style.display = numero > 0 ? "flex" : "none"; // 'flex' para mejor centrado si es necesario
    }

    async function manejarClickNotificacion(notificacion) {
        // Si ya está leída y tiene un enlace, simplemente redirige
        if (notificacion.estado_notificacion === "leída" && notificacion.enlace) {
            window.location.href = notificacion.enlace;
            notificacionesDropdown.style.display = "none";
            return;
        }

        // Marcar como leída en el backend si no está leída
        if (notificacion.estado_notificacion === "no leída") {
            try {
                const response = await fetch(API_URL_MARCAR_LEIDA, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        id_notificacion: notificacion.id_notificacion,
                    }), // id_usuario se toma de la sesión en PHP
                });
                const result = await response.json();
                if (result.success) {
                    // Actualizar UI localmente o recargar
                    // document.querySelector(`li[data-id="${notificacion.id_notificacion}"]`).classList.remove('no-leida');
                    // document.querySelector(`li[data-id="${notificacion.id_notificacion}"]`).classList.add('leida');
                    // Opcionalmente, disminuir el contador si solo se actualiza la UI sin recargar todo
                    cargarNotificaciones(); // Recargar para reflejar el cambio y el contador
                } else {
                    console.error("Error al marcar como leída:", result.message);
                }
            } catch (error) {
                console.error("Error de red al marcar como leída:", error);
            }
        }

        // Redirigir si hay enlace
        if (notificacion.enlace) {
            window.location.href = notificacion.enlace;
        }
        notificacionesDropdown.style.display = "none"; // Ocultar dropdown
    }

    if (notificacionesIcono) {
        notificacionesIcono.addEventListener("click", (event) => {
            event.stopPropagation(); // Evitar que un clic en el body lo cierre inmediatamente
            const display = window.getComputedStyle(notificacionesDropdown).display;
            notificacionesDropdown.style.display =
                display === "none" ? "block" : "none";
            if (notificacionesDropdown.style.display === "block") {
                cargarNotificaciones(); // Cargar/recargar al abrir
            }
        });
    }

    if (marcarTodasLeidasBtn) {
        marcarTodasLeidasBtn.addEventListener("click", async () => {
            try {
                const response = await fetch(API_URL_MARCAR_TODAS, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    // No es necesario enviar id_usuario en el body si se toma de la sesión PHP
                });
                const result = await response.json();
                if (result.success) {
                    cargarNotificaciones(); // Recargar para reflejar los cambios
                } else {
                    console.error("Error al marcar todas como leídas:", result.message);
                    alert("Error al marcar todas como leídas.");
                }
            } catch (error) {
                console.error("Error de red al marcar todas como leídas:", error);
                alert("Error de red. Intenta de nuevo.");
            }
        });
    }

    // Cerrar dropdown si se hace clic fuera
    document.addEventListener("click", function (event) {
        if (notificacionesDropdown && notificacionesIcono) {
            const isClickInside =
                notificacionesIcono.contains(event.target) ||
                notificacionesDropdown.contains(event.target);
            if (
                !isClickInside &&
                window.getComputedStyle(notificacionesDropdown).display === "block"
            ) {
                notificacionesDropdown.style.display = "none";
            }
        }
    });

    // Carga inicial y polling
    if (idUsuarioActual) {
        cargarNotificaciones(); // Carga inicial
        setInterval(cargarNotificaciones, POLLING_INTERVAL);
    }
});
