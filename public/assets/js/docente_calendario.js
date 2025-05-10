document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const eventModal = document.getElementById('event-modal');
    const newEventModal = document.getElementById('new-event-modal');
    const closeButtons = document.querySelectorAll('.close-modal-btn');
    const eventForm = document.getElementById('event-form');
    let currentClickedEventInfo = null; // Para guardar la info del evento clickeado

    // --- RUTAS A HANDLERS ---
    const RUTA_GET_EVENTS = '../src/modules/docente_calendario/get_events_handler.php';
    const RUTA_ADD_EVENT = '../src/modules/docente_calendario/add_event_handler.php';
    const RUTA_UPDATE_EVENT = '../src/modules/docente_calendario/update_event_handler.php';
    const RUTA_DELETE_EVENT = '../src/modules/docente_calendario/delete_event_handler.php';

    // Función para formatear fecha para input datetime-local
    function formatDateTimeLocal(date) {
        const d = new Date(date);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset()); // Ajustar a la zona horaria local
        return d.toISOString().slice(0, 16);
    }
    
    // Función para parsear la fecha y hora de los inputs locales a UTC para FullCalendar
    function parseDateTimeLocal(dateTimeLocalString) {
        if (!dateTimeLocalString) return null;
        return new Date(dateTimeLocalString).toISOString();
    }

    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: false,
        firstDay: 1,
        editable: true, // Permitir arrastrar y redimensionar eventos
        selectable: true, // Permitir seleccionar fechas/horas para crear eventos

        events: function(fetchInfo, successCallback, failureCallback) {
            fetch(RUTA_GET_EVENTS)
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error cargando eventos:', error);
                    failureCallback(error);
                });
        },
        
        select: function(selectionInfo) {
            document.getElementById('form-event-title-modal').innerText = 'Nuevo Evento';
            eventForm.reset();
            document.getElementById('event-id').value = ''; // Limpiar ID para nuevo evento
            
            // Formatear fecha de inicio y fin para el modal
            // FullCalendar devuelve start/end como Date objects
            document.getElementById('event-start-input').value = formatDateTimeLocal(selectionInfo.start);
            if (selectionInfo.allDay) {
                 // Si es allDay, el final es exclusivo, ajustamos para que sea inclusivo si se desea un solo día
                let endDate = new Date(selectionInfo.end);
                endDate.setDate(endDate.getDate() -1); // Para que el modal muestre el día correcto si es un solo día
                document.getElementById('event-end-input').value = formatDateTimeLocal(endDate);
            } else {
                document.getElementById('event-end-input').value = formatDateTimeLocal(selectionInfo.end);
            }
            
            newEventModal.style.display = 'block';
        },

        eventClick: function(info) {
            currentClickedEventInfo = info; // Guardar info del evento
            document.getElementById('event-title-modal').textContent = info.event.title;
            
            let dateStr = '';
            if (info.event.allDay) {
                dateStr = info.event.start.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            } else {
                dateStr = info.event.start.toLocaleString('es-ES', { 
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', 
                    hour: '2-digit', minute: '2-digit' 
                });
                if (info.event.end) {
                    dateStr += ' - ' + info.event.end.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                }
            }
            
            document.getElementById('event-date-modal').innerHTML = `<span>${dateStr}</span>`;
            document.getElementById('event-description-modal').innerHTML = `<span>${info.event.extendedProps.description || 'Sin descripción'}</span>`;
            
            eventModal.style.display = 'block';
        },

        eventDrop: function(info) { // Cuando un evento es arrastrado a una nueva fecha/hora
            updateEventHandler(info.event);
        },

        eventResize: function(info) { // Cuando un evento es redimensionado
            updateEventHandler(info.event);
        },

        eventDidMount: function(info) {
            if (info.event.backgroundColor) {
                info.el.style.backgroundColor = info.event.backgroundColor;
                info.el.style.borderColor = info.event.backgroundColor; // Para consistencia
            }
        }
    });

    calendar.render();
    updateMonthYearDisplay(calendar);

    function updateMonthYearDisplay(calInstance) {
        const view = calInstance.view;
        document.getElementById('month-year').textContent = view.title;
    }

    // --- MANEJADORES DE EVENTOS DEL DOM ---

    document.getElementById('today-btn').addEventListener('click', function() {
        calendar.today();
        updateMonthYearDisplay(calendar);
    });

    document.getElementById('prev-btn').addEventListener('click', function() {
        calendar.prev();
        updateMonthYearDisplay(calendar);
    });

    document.getElementById('next-btn').addEventListener('click', function() {
        calendar.next();
        updateMonthYearDisplay(calendar);
    });

    document.getElementById('day-view').addEventListener('click', function() {
        calendar.changeView('timeGridDay');
        setActiveViewButton('day');
    });

    document.getElementById('week-view').addEventListener('click', function() {
        calendar.changeView('timeGridWeek');
        setActiveViewButton('week');
    });

    document.getElementById('month-view').addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
        setActiveViewButton('month');
    });

    function setActiveViewButton(viewName) {
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(`${viewName}-view`).classList.add('active');
    }

    document.getElementById('new-event-btn').addEventListener('click', function() {
        document.getElementById('form-event-title-modal').innerText = 'Nuevo Evento';
        eventForm.reset();
        document.getElementById('event-id').value = '';
        const now = new Date();
        document.getElementById('event-start-input').value = formatDateTimeLocal(now);
        newEventModal.style.display = 'block';
    });
    
    // Botón Editar en el modal de visualización
    document.getElementById('edit-event-btn-modal').addEventListener('click', function() {
        if (currentClickedEventInfo) {
            const event = currentClickedEventInfo.event;
            eventModal.style.display = 'none'; // Ocultar modal de visualización

            document.getElementById('form-event-title-modal').innerText = 'Editar Evento';
            document.getElementById('event-id').value = event.id;
            document.getElementById('event-name-input').value = event.title;
            document.getElementById('event-start-input').value = formatDateTimeLocal(event.start);
            document.getElementById('event-end-input').value = event.end ? formatDateTimeLocal(event.end) : '';
            document.getElementById('event-description-input').value = event.extendedProps.description || '';
            document.getElementById('event-color-input').value = event.backgroundColor || '#3eb489';
            
            newEventModal.style.display = 'block'; // Mostrar modal de edición/creación
        }
    });


    eventForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const eventId = document.getElementById('event-id').value;
        const title = document.getElementById('event-name-input').value;
        const start = parseDateTimeLocal(document.getElementById('event-start-input').value);
        const end = parseDateTimeLocal(document.getElementById('event-end-input').value);
        const description = document.getElementById('event-description-input').value;
        const color = document.getElementById('event-color-input').value;

        const eventData = {
            id: eventId || null, // Enviar null si es nuevo, o el ID si es existente
            title: title,
            start: start,
            end: end,
            description: description,
            backgroundColor: color,
            allDay: false // Asumimos que no son allDay a menos que se especifique lo contrario
            // Puedes añadir un checkbox para 'allDay' y ajustar esto
        };

        const RUTA = eventId ? RUTA_UPDATE_EVENT : RUTA_ADD_EVENT;

        fetch(RUTA, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                calendar.refetchEvents(); // Recargar todos los eventos
                newEventModal.style.display = 'none';
            } else {
                alert('Error al guardar el evento: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => console.error('Error al guardar evento:', error));
    });

    document.getElementById('delete-event-btn').addEventListener('click', function() {
        if (currentClickedEventInfo && confirm('¿Estás seguro de eliminar este evento?')) {
            const eventId = currentClickedEventInfo.event.id;
            fetch(RUTA_DELETE_EVENT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: eventId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    currentClickedEventInfo.event.remove();
                    eventModal.style.display = 'none';
                } else {
                    alert('Error al eliminar el evento: ' + (data.message || 'Error desconocido' ));
                }
            })
            .catch(error => console.error('Error al eliminar evento:', error));
        }
    });

    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            eventModal.style.display = 'none';
            newEventModal.style.display = 'none';
        });
    });

    window.addEventListener('click', function(event) {
        if (event.target === eventModal) eventModal.style.display = 'none';
        if (event.target === newEventModal) newEventModal.style.display = 'none';
    });

    // --- Mini Calendario ---
    const miniCalendarEl = document.getElementById('mini-calendar');
    if (miniCalendarEl) {
        const miniCalendar = new FullCalendar.Calendar(miniCalendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: {
                start: 'title',
                center: '',
                end: 'today prev,next'
            },
            height: 'auto',
            dateClick: function(info) {
                calendar.gotoDate(info.date);
                updateMonthYearDisplay(calendar);
            }
        });
        miniCalendar.render();

        // Sincronización entre calendarios
        let syncing = false;
        calendar.on('datesSet', function() {
            if (!syncing) {
                syncing = true;
                miniCalendar.gotoDate(calendar.getDate());
                updateMonthYearDisplay(calendar); // Asegurar que el título del principal también se actualice
                setTimeout(() => syncing = false, 100); // Aumentar un poco el timeout
            }
        });
        miniCalendar.on('datesSet', function() { // Usar el evento 'datesSet' para el mini calendario también
            if (!syncing) {
                syncing = true;
                calendar.gotoDate(miniCalendar.getDate());
                updateMonthYearDisplay(calendar);
                setTimeout(() => syncing = false, 100);
            }
        });
    } else {
        console.warn("Elemento #mini-calendar no encontrado.");
    }
    
    // Función para manejar la actualización de eventos (arrastrar, redimensionar)
    function updateEventHandler(event) {
        const eventData = {
            id: event.id,
            title: event.title,
            start: event.start.toISOString(),
            end: event.end ? event.end.toISOString() : null,
            // allDay: event.allDay // FullCalendar maneja esto
            // No es necesario enviar 'backgroundColor' o 'description' aquí a menos que también se modifiquen
        };

        fetch(RUTA_UPDATE_EVENT, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Error al actualizar el evento: ' + (data.message || 'Error desconocido'));
                // Revertir el cambio visual si la actualización falla
                info.revert();
            }
        })
        .catch(error => {
            console.error('Error al actualizar evento:', error);
            info.revert();
        });
    }
});