document.addEventListener('DOMContentLoaded', function () {
    // --- FUNCIONES AUXILIARES ---
    function formatDateTimeLocal(date) {
        const d = new Date(date);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    }

    function updateMonthYear(calendarInstance) {
        document.getElementById('month-year').textContent = calendarInstance.view.title;
    }

    // --- CALENDARIO PRINCIPAL ---
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: false,
        firstDay: 1,
        editable: true,
        events: function (fetchInfo, successCallback, failureCallback) {
            fetch('get-events.php')
                .then(response => response.json())
                .then(data => {
                    if (Array.isArray(data)) {
                        successCallback(data);
                    } else {
                        console.error('La respuesta de get-events.php no es un array:', data);
                        failureCallback(new Error('Formato de respuesta inesperado.'));
                    }
                })
                .catch(error => {
                    console.error('Error al cargar eventos:', error);
                    alert('No se pudieron cargar los eventos del servidor.');
                    failureCallback(error);
                });
        },
        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        dateClick: function (info) {
            document.getElementById('event-form').reset();
            const startDate = new Date(info.date);
            startDate.setHours(9, 0, 0, 0);
            document.getElementById('event-start').value = formatDateTimeLocal(startDate);
            document.getElementById('new-event-modal').style.display = 'block';
        },
        eventClick: function (info) {
            const event = info.event;
            document.getElementById('event-title').textContent = event.title;

            let dateStr = event.start.toLocaleString('es-ES', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
            if (event.end) {
                dateStr += ' - ' + event.end.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
            }
            document.getElementById('event-date').innerHTML = `<span>${dateStr}</span>`;
            
            const descriptionHtml = event.extendedProps.description ? `<span>${event.extendedProps.description}</span>` : '<span>Sin descripción</span>';
            document.getElementById('view-event-description').innerHTML = descriptionHtml;
            
            document.getElementById('delete-event').onclick = function () {
                if (confirm('¿Estás seguro de eliminar este evento?')) {
                    fetch('delete-event.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: event.id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            event.remove();
                            document.getElementById('event-modal').style.display = 'none';
                        } else {
                            alert('Error al eliminar: ' + (data.message || 'Error desconocido.'));
                        }
                    });
                }
            };
            document.getElementById('event-modal').style.display = 'block';
        },
        eventDrop: function (info) { updateEventOnServer(info.event); },
        eventResize: function (info) { updateEventOnServer(info.event); }
    });

    calendar.render();
    updateMonthYear(calendar);

    function updateEventOnServer(event) {
        const eventData = {
            id: event.id,
            title: event.title,
            description: event.extendedProps.description || '',
            start: event.start.toISOString(),
            end: event.end ? event.end.toISOString() : null,
            backgroundColor: event.backgroundColor
        };
        fetch('update-event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Error al actualizar evento: ' + data.message);
                info.revert();
            }
        }).catch(() => {
            alert('Error de conexión al actualizar.');
            info.revert();
        });
    }

    // --- MANEJADORES DE EVENTOS PARA BOTONES Y MODALES ---
    document.getElementById('today-btn').addEventListener('click', () => { calendar.today(); updateMonthYear(calendar); });
    document.getElementById('prev-btn').addEventListener('click', () => { calendar.prev(); updateMonthYear(calendar); });
    document.getElementById('next-btn').addEventListener('click', () => { calendar.next(); updateMonthYear(calendar); });
    document.getElementById('day-view').addEventListener('click', () => calendar.changeView('timeGridDay'));
    document.getElementById('week-view').addEventListener('click', () => calendar.changeView('timeGridWeek'));
    document.getElementById('month-view').addEventListener('click', () => calendar.changeView('dayGridMonth'));
    document.getElementById('new-event-btn').addEventListener('click', () => {
        document.getElementById('event-form').reset();
        const now = new Date();
        now.setHours(9,0,0,0);
        document.getElementById('event-start').value = formatDateTimeLocal(now);
        document.getElementById('new-event-modal').style.display = 'block';
    });

    // CIERRE DE MODALES
    document.querySelectorAll('.modal .close').forEach(button => {
        button.onclick = () => { button.closest('.modal').style.display = 'none'; };
    });
    window.onclick = (event) => {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    };

    // --- MANEJADOR DEL FORMULARIO DE NUEVO EVENTO ---
    document.getElementById('event-form').addEventListener('submit', function (e) {
        e.preventDefault();

        // Esta sección recolecta TODOS los datos del formulario.
        const eventDataForBackend = {
            title: document.getElementById('event-name').value,
            description: document.getElementById('form-event-description').value,
            start: document.getElementById('event-start').value,
            end: document.getElementById('event-end').value || null,
            color: document.getElementById('event-color').value,
            categoria_evento: document.getElementById('event-category').value
        };

        // Esta sección envía los datos al servidor.
        fetch('add-event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventDataForBackend),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                calendar.refetchEvents();
                document.getElementById('new-event-modal').style.display = 'none';
                this.reset();
            } else {
                alert('Error al guardar: ' + (data.message || 'Error desconocido.'));
            }
        })
        .catch(error => {
            console.error('Error en AJAX:', error);
            alert('Error de conexión al guardar el evento.');
        });
    });

    // --- MINI-CALENDARIO ---
    const miniCalendarEl = document.getElementById('mini-calendar');
    let miniCalendarInstance = null;

    if (miniCalendarEl) {
        miniCalendarInstance = new FullCalendar.Calendar(miniCalendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: {
                start: 'title',
                center: '',
                end: 'prev,next'
            },
            height: 'auto',
            dateClick: function (info) {
                calendar.gotoDate(info.date);
                updateMonthYear(calendar);
            }
        });
        miniCalendarInstance.render();
    }

    // --- SINCRONIZACIÓN ENTRE CALENDARIOS ---
    let syncing = false;
    calendar.on('datesSet', function (dateInfo) {
        if (!syncing && miniCalendarInstance) {
            syncing = true;
            miniCalendarInstance.gotoDate(calendar.getDate());
            updateMonthYear(calendar);
            setTimeout(() => syncing = false, 50);
        }
    });

    if (miniCalendarInstance) {
        miniCalendarInstance.on('dateClick', function (info) {
            if (!syncing) {
                syncing = true;
                calendar.gotoDate(info.date);
                setTimeout(() => syncing = false, 50);
            }
        });
    }
    // ... todo tu código existente de calendario.js ...

    // --- INICIO DEL NUEVO CÓDIGO ---
    // Este código se ejecuta cuando la página carga para abrir un evento desde la URL
    function abrirEventoDesdeUrl() {
        const urlParams = new URLSearchParams(window.location.search);
        const eventIdFromUrl = urlParams.get('id_evento');

        if (eventIdFromUrl) {
            // Esperamos un momento para que FullCalendar cargue los eventos desde el servidor.
            // 1000 milisegundos (1 segundo) suele ser suficiente.
            setTimeout(() => {
                const eventToOpen = calendar.getEventById(eventIdFromUrl);
                
                if (eventToOpen) {
                    // Si encontramos el evento, simulamos un clic en él para abrir el modal.
                    calendar.trigger('eventClick', {
                        event: eventToOpen,
                        el: eventToOpen.el, // Elemento HTML del evento
                        jsEvent: new MouseEvent('click'), // Simula un evento de clic
                        view: calendar.view
                    });
                } else {
                    console.warn(`No se encontró el evento con ID ${eventIdFromUrl} en el calendario.`);
                }
            }, 1000); 
        }
    }

    abrirEventoDesdeUrl();
    // --- FIN DEL NUEVO CÓDIGO ---

}); // <-- Esta es la última línea de tu archivo, el nuevo código va justo antes.