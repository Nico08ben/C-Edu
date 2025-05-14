document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');
    const defaultEventColorDocente = '#3eb489'; // Verde para Docente
    const defaultEventColorAdmin = '#e7bb41';   // Amarillo para Admin

    // Determinar el color por defecto basado en la URL (simple heurística)
    // Puedes mejorarlo pasando una variable desde PHP a JS si es necesario.
    const isUserAdmin = window.location.pathname.includes('/Administrador/');
    const defaultEventColor = isUserAdmin ? defaultEventColorAdmin : defaultEventColorDocente;
    const defaultEventTextColor = getContrastYIQ(defaultEventColor);


    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: false,
        firstDay: 1,
        editable: true, // Permite arrastrar y redimensionar eventos
        selectable: true, // Permite seleccionar fechas para crear eventos (opcional)

        // Cargar eventos desde el backend
        events: 'get-events.php', // URL al script PHP

        eventDisplay: 'block',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },

        // Al hacer clic en una fecha/hora del calendario
        select: function(selectionInfo) {
            // El modal se abrirá con valores por defecto o la fecha seleccionada
            document.getElementById('event-form').reset(); // Limpiar formulario
            document.getElementById('event-start').value = formatDateTimeLocal(selectionInfo.start);
            if (selectionInfo.allDay) {
                 // Si es allDay, la hora final podría ser el inicio del día siguiente.
                 // Para un evento de día completo, a menudo no se especifica hora final o es la misma fecha.
                document.getElementById('event-end').value = formatDateTimeLocal(selectionInfo.start); // O dejar vacío si se maneja en backend
            } else {
                document.getElementById('event-end').value = selectionInfo.end ? formatDateTimeLocal(selectionInfo.end) : '';
            }
            document.getElementById('event-color').value = defaultEventColor;
            document.getElementById('new-event-modal').style.display = 'block';
        },
        
        // Al hacer clic en un evento existente
        eventClick: function(info) {
            const event = info.event;
            document.getElementById('event-title').textContent = event.title;

            let dateStr = '';
            if (event.allDay) {
                dateStr = event.start.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            } else {
                dateStr = event.start.toLocaleString('es-ES', {
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });
                if (event.end) {
                    dateStr += ' - ' + event.end.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                }
            }
            document.getElementById('event-date').innerHTML = `<span>${dateStr}</span>`;
            const descriptionText = event.extendedProps.description || 'Sin descripción';
            document.getElementById('event-description').innerHTML = `<span>${descriptionText.replace(/\n/g, '<br>')}</span>`;


            // Configurar botón de eliminar
            document.getElementById('delete-event').onclick = function() {
                if (confirm('¿Estás seguro de eliminar este evento?')) {
                    fetch('delete-event.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id: event.id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            info.event.remove(); // Eliminar del UI
                            document.getElementById('event-modal').style.display = 'none';
                            alert('Evento eliminado correctamente.');
                        } else {
                            alert('Error al eliminar el evento: ' + (data.message || 'Error desconocido'));
                        }
                    })
                    .catch(error => {
                        console.error('Error en fetch (delete):', error);
                        alert('Error de conexión al eliminar el evento.');
                    });
                }
            };
            document.getElementById('event-modal').style.display = 'block';
        },

        // Cuando un evento se arrastra a una nueva fecha/hora
        eventDrop: function(info) {
            if (!confirm("¿Estás seguro de mover este evento?")) {
                info.revert(); // Revertir el cambio en la UI si el usuario cancela
                return;
            }
            const eventData = {
                id: info.event.id,
                title: info.event.title,
                start: info.event.start.toISOString(), // Enviar en formato ISO
                end: info.event.end ? info.event.end.toISOString() : null,
                description: info.event.extendedProps.description || "",
                // Aquí podrías enviar el color si lo tienes y lo quieres actualizar
                // backgroundColor: info.event.backgroundColor
            };

            fetch('update-event.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(eventData)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Error al actualizar el evento: ' + (data.message || 'Error desconocido'));
                    info.revert(); // Revertir si la actualización falla en el backend
                } else {
                    alert('Evento actualizado correctamente.');
                    calendar.refetchEvents(); // Opcional: Refrescar todos los eventos
                }
            })
            .catch(error => {
                console.error('Error en fetch (update on drop):', error);
                alert('Error de conexión al actualizar el evento.');
                info.revert();
            });
        },
        
        // (Opcional) Cuando un evento se redimensiona
        // eventResize: function(info) { ... similar a eventDrop ... },

        // ... dentro de la configuración de FullCalendar.Calendar en calendario.js ...
        eventDidMount: function(info) {
            const eventColor = info.event.backgroundColor || defaultEventColor; // Usa color del evento o el default de la sección
            const eventTextColor = getContrastYIQ(eventColor);

            info.el.style.backgroundColor = eventColor;
            info.el.style.borderLeft = `4px solid ${eventColor}`;
            info.el.style.color = eventTextColor;
        },
// ...
    });

    calendar.render();
    updateMonthYear(calendar);

    function formatDateTimeLocal(date) {
        if (!date) return '';
        const d = new Date(date);
        // Ajustar a la zona horaria local para la visualización en el input datetime-local
        const offset = d.getTimezoneOffset() * 60000;
        const localDate = new Date(d.getTime() - offset);
        return localDate.toISOString().slice(0, 16);
    }

    function updateMonthYear(calInstance) {
        const view = calInstance.view;
        document.getElementById('month-year').textContent = view.title;
    }

    function getContrastYIQ(hexcolor){
        if (!hexcolor) return '#333333'; // Color de texto por defecto si no hay color de fondo
        hexcolor = hexcolor.replace("#", "");
        var r = parseInt(hexcolor.substr(0,2),16);
        var g = parseInt(hexcolor.substr(2,2),16);
        var b = parseInt(hexcolor.substr(4,2),16);
        var yiq = ((r*299)+(g*587)+(b*114))/1000;
        return (yiq >= 135) ? '#333333' : '#FFFFFF'; // Umbral ajustado ligeramente
    }

    document.getElementById('today-btn').addEventListener('click', function() {
        calendar.today();
        updateMonthYear(calendar);
    });
    document.getElementById('prev-btn').addEventListener('click', function() {
        calendar.prev();
        updateMonthYear(calendar);
    });
    document.getElementById('next-btn').addEventListener('click', function() {
        calendar.next();
        updateMonthYear(calendar);
    });
    document.getElementById('day-view').addEventListener('click', function() {
        calendar.changeView('timeGridDay');
        setActiveView('day');
    });
    document.getElementById('week-view').addEventListener('click', function() {
        calendar.changeView('timeGridWeek');
        setActiveView('week');
    });
    document.getElementById('month-view').addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
        setActiveView('month');
    });

    function setActiveView(view) {
        document.querySelectorAll('.view-btn').forEach(btn => btn.classList.remove('active'));
        document.getElementById(`${view}-view`).classList.add('active');
    }

    document.getElementById('new-event-btn').addEventListener('click', function() {
        document.getElementById('event-form').reset(); // Limpiar formulario
        const now = new Date();
        document.getElementById('event-start').value = formatDateTimeLocal(now);
        document.getElementById('event-color').value = defaultEventColor; // Color por defecto de la sección
        // El ID del textarea de descripción en Admin es 'event-description-admin' y en Docente 'event-description'
        const descriptionTextareaId = isUserAdmin ? 'event-description-admin' : 'event-description';
        if (document.getElementById(descriptionTextareaId)) { //Verificar que el elemento existe
             document.getElementById(descriptionTextareaId).value = '';
        }
        document.getElementById('new-event-modal').style.display = 'block';
    });

    document.getElementById('event-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const title = document.getElementById('event-name').value;
        const start = document.getElementById('event-start').value;
        const end = document.getElementById('event-end').value || null; // Enviar null si está vacío
        const descriptionTextareaId = isUserAdmin ? 'event-description-admin' : 'event-description';
        const description = document.getElementById(descriptionTextareaId) ? document.getElementById(descriptionTextareaId).value : "";
        const backgroundColor = document.getElementById('event-color').value;

        if (!title || !start) {
            alert("El título y la fecha de inicio son obligatorios.");
            return;
        }
        
        const eventData = {
            title: title,
            start: start, // YYYY-MM-DDTHH:MM
            end: end,     // YYYY-MM-DDTHH:MM o null
            description: description,
            backgroundColor: backgroundColor // Se envía al backend
        };

        fetch('add-event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // El backend debería devolver el evento completo, incluyendo el nuevo ID y color si se guardó.
                // Si add-event.php devuelve el evento con su ID de la BD:
                calendar.addEvent({
                    id: data.id, // ID de la base de datos
                    title: title,
                    start: start,
                    end: end,
                    description: description,
                    backgroundColor: backgroundColor,
                    textColor: getContrastYIQ(backgroundColor)
                });
                document.getElementById('new-event-modal').style.display = 'none';
                this.reset();
                alert('Evento añadido correctamente.');
            } else {
                alert('Error al añadir el evento: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error en fetch (add):', error);
            alert('Error de conexión al añadir el evento.');
        });
    });

    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    const miniCalendarEl = document.getElementById('mini-calendar');
    if (miniCalendarEl) { // Verificar que el mini-calendario existe
        const miniCalendar = new FullCalendar.Calendar(miniCalendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: { start: 'title', center: '', end: 'today prev,next' },
            height: 'auto',
            dateClick: function(info) {
                calendar.gotoDate(info.date);
                updateMonthYear(calendar);
            }
        });
        miniCalendar.render();

        let syncing = false;
        calendar.on('datesSet', function() {
            if (!syncing) {
                syncing = true;
                miniCalendar.gotoDate(calendar.getDate());
                setTimeout(() => syncing = false, 50);
            }
        });
    }
});