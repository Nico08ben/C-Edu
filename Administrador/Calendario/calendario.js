document.addEventListener('DOMContentLoaded', function() {
    // Obtener eventos guardados en localStorage
    function getSavedEvents() {
        const savedEvents = localStorage.getItem('calendarEvents');
        return savedEvents ? JSON.parse(savedEvents) : [];
    }

    // Guardar eventos en localStorage
    function saveEvents(events) {
        localStorage.setItem('calendarEvents', JSON.stringify(events));
    }

    // FullCalendar variables (unchanged)
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: false, // Ocultamos la barra de herramientas nativa
        firstDay: 1, // Lunes como primer día de la semana
        events: function(fetchInfo, successCallback, failureCallback) {
            // Cargar eventos desde get-events.php
            fetch('get-events.php')
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error del servidor al obtener eventos: ${response.status} ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Comprobar si la respuesta es un array (incluso vacío)
                    if (Array.isArray(data)) {
                        successCallback(data);
                        saveEvents(data); // Guardar en localStorage también
                    } else {
                        console.error('Respuesta de get-events.php no es un array:', data);
                        failureCallback(new Error('Formato de respuesta inesperado al obtener eventos.'));
                    }
                })
                .catch(error => {
                    console.error('Error al cargar eventos desde get-events.php:', error);
                    // Intentar cargar desde localStorage como fallback
                    const localEvents = getSavedEvents();
                    console.log('Cargando eventos desde localStorage como fallback:', localEvents);
                    successCallback(localEvents);
                    alert('No se pudieron cargar los eventos del servidor. Mostrando eventos guardados localmente, si existen.');
                    failureCallback(error);
                });
        },
        eventDisplay: 'block',
        eventColor: '#3eb489', // Color por defecto
        eventTextColor: '#333', // Texto más oscuro para mejor contraste con colores claros
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true // O false si prefieres formato de 24 horas
        },
        dateClick: function(info) {
            // Abrir modal para nuevo evento al hacer clic en una fecha
            const startDate = new Date(info.date);
            // Establecer hora por defecto (ej. 09:00 AM del día clickeado)
            startDate.setHours(9, 0, 0, 0);

            document.getElementById('event-start').value = formatDateTimeLocal(startDate);
            document.getElementById('event-end').value = ''; // Limpiar fecha de fin
            document.getElementById('event-name').value = ''; // Limpiar campos del formulario
            document.getElementById('event-description-admin').value = ''; // Usar el ID correcto para la descripción del admin
            document.getElementById('event-color').value = '#e7bb41'; // Color por defecto (Amarillo Admin)

            document.getElementById('new-event-modal').style.display = 'block';
        },
        eventClick: function(info) {
            // Mostrar detalles del evento al hacer clic en él
            const event = info.event;
            document.getElementById('event-title').textContent = event.title;

            let dateStr = '';
            if (event.allDay) { // FullCalendar a veces no establece allDay explícitamente
                const start = event.start;
                if (start) {
                   dateStr = start.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                }
            } else {
                const start = event.start;
                const end = event.end;
                if (start) {
                    dateStr = start.toLocaleString('es-ES', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
                if (end) {
                    dateStr += ' - ' + end.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                } else if (start && !end) { // Si solo hay start, mostrar solo la hora de inicio
                     // La fecha ya está incluida, solo asegurar que se muestre
                }
            }

            document.getElementById('event-date').innerHTML = `<span>${dateStr || 'Fecha no especificada'}</span>`;
            document.getElementById('event-description').innerHTML = `<span>${event.extendedProps.description || 'Sin descripción'}</span>`;

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
                            event.remove();
                            // Actualizar localStorage
                            const savedEvents = getSavedEvents().filter(e => e.id !== event.id);
                            saveEvents(savedEvents);
                            document.getElementById('event-modal').style.display = 'none';
                        } else {
                            alert('Error al eliminar el evento: ' + (data.message || 'Error desconocido.'));
                        }
                    })
                    .catch(error => {
                        console.error('Error al eliminar evento (AJAX):', error);
                        alert('Error de conexión al eliminar el evento.');
                    });
                }
            };

            document.getElementById('event-modal').style.display = 'block';
        },
        eventDidMount: function(info) {
            // Establecer color del evento
            if (info.event.backgroundColor) {
                info.el.style.backgroundColor = info.event.backgroundColor;
                info.el.style.borderLeft = `4px solid ${info.event.backgroundColor}`;
            }
             // Asegurar que el texto del evento sea visible si el color de fondo es oscuro
            const bgColor = info.event.backgroundColor || '#3eb489'; // Color por defecto si no está definido
            const brightness = getBrightness(bgColor); // Usando nuestra función simple
            if (brightness < 0.5) { // Si el fondo es oscuro
                info.el.style.color = 'white'; // Poner texto en blanco
            } else {
                info.el.style.color = '#333'; // Poner texto oscuro por defecto
            }
        },
        editable: true, // Permitir arrastrar y redimensionar eventos
        eventDrop: function(info) { // Cuando un evento es arrastrado a una nueva fecha/hora
            updateEventOnServer(info.event);
        },
        eventResize: function(info) { // Cuando un evento es redimensionado
            updateEventOnServer(info.event);
        }
    });

    calendar.render();
    updateMonthYear(calendar);

    // Función para actualizar evento en el servidor (y localStorage)
    function updateEventOnServer(event) {
        const eventData = {
            id: event.id,
            title: event.title,
            start: event.start.toISOString(), // Formato ISO para el backend
            end: event.end ? event.end.toISOString() : null,
            description: event.extendedProps.description || ''
            // No enviamos color aquí, ya que no parece ser parte de la tabla `evento`
        };

        fetch('update-event.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Evento actualizado en el servidor');
                // Actualizar en localStorage
                const savedEvents = getSavedEvents().map(e => e.id === event.id ? {
                    ...e, // Mantener propiedades existentes como el color
                    id: event.id, // Asegurar que el id sea el correcto (string o int según DB)
                    title: event.title,
                    start: event.start.toISOString(),
                    end: event.end ? event.end.toISOString() : undefined, // FullCalendar usa undefined para eventos sin fin
                    description: event.extendedProps.description,
                    // backgroundColor y borderColor ya deberían estar en el evento de FullCalendar
                    backgroundColor: event.backgroundColor,
                    borderColor: event.borderColor
                } : e);
                saveEvents(savedEvents);
            } else {
                alert('Error al actualizar el evento en el servidor: ' + (data.message || 'Error desconocido.'));
                info.revert(); // Revertir el cambio en el calendario si falla la actualización
            }
        })
        .catch(error => {
            console.error('Error al actualizar evento (AJAX):', error);
            alert('Error de conexión al actualizar el evento.');
            info.revert();
        });
    }


    // Función para formatear fecha para input datetime-local
    function formatDateTimeLocal(date) {
        const d = new Date(date);
        // Ajustar a la zona horaria local para la visualización en el input
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    }

    // Actualizar el mes y año mostrado
    function updateMonthYear(calendarInstance) {
        const view = calendarInstance.view;
        if (view && view.title) {
            document.getElementById('month-year').textContent = view.title;
        }
    }

    // Configurar botones de navegación
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

    // Configurar botones de vista
    document.getElementById('day-view').addEventListener('click', function() {
        calendar.changeView('timeGridDay');
        setActiveView('day');
        updateMonthYear(calendar);
    });

    document.getElementById('week-view').addEventListener('click', function() {
        calendar.changeView('timeGridWeek');
        setActiveView('week');
        updateMonthYear(calendar);
    });

    document.getElementById('month-view').addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
        setActiveView('month');
        updateMonthYear(calendar);
    });

    // Función para marcar la vista activa
    function setActiveView(view) {
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const activeBtn = document.getElementById(`${view}-view`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }

    // Configurar modal de nuevo evento
    document.getElementById('new-event-btn').addEventListener('click', function() {
        const now = new Date();
        now.setHours(9,0,0,0); // Hora por defecto: 09:00 AM

        document.getElementById('event-start').value = formatDateTimeLocal(now);
        document.getElementById('event-end').value = '';
        document.getElementById('event-name').value = '';
        document.getElementById('event-description-admin').value = ''; // Usar el ID correcto
        document.getElementById('event-color').value = '#e7bb41'; // Color por defecto (Amarillo Admin)

        document.getElementById('new-event-modal').style.display = 'block';
    });

    // ******************************************************************
    // INICIO DE LA INTEGRACIÓN DE LA NOTIFICACIÓN
    // ******************************************************************

    // Definir las variables de la notificación fuera del listener, pero dentro del DOMContentLoaded
    // para que sean accesibles. Estas vienen de tu script.js original.
    const toast = document.querySelector(".toast");
    const closeIcon = document.querySelector(".toast .close"); // Más específico para evitar colisiones
    const progress = document.querySelector(".progress");

    // Lógica para cerrar la notificación al hacer clic en la "x"
    if (closeIcon) { // Asegurarse de que el elemento existe antes de añadir el listener
        closeIcon.addEventListener("click", () => {
            if (toast) toast.classList.remove("active");
            // Dar un pequeño retraso para que la barra de progreso se oculte correctamente
            setTimeout(() => {
                if (progress) progress.classList.remove("active");
            }, 300);
        });
    }

    // Manejar envío del formulario de nuevo evento
    document.getElementById('event-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const title = document.getElementById('event-name').value;
        const start = document.getElementById('event-start').value;
        const end = document.getElementById('event-end').value;
        // Asegúrate de usar el ID correcto para la descripción si es `event-description-admin`
        const description = document.getElementById('event-description-admin').value;
        const color = document.getElementById('event-color').value;

        const newEventDataForFullCalendar = {
            id: 'event_' + Date.now(), // Un ID temporal antes de obtener el de la BD
            title: title,
            start: start,
            end: end || undefined, // undefined si no hay fecha de fin
            description: description,
            backgroundColor: color,
            borderColor: color // Usa el mismo color para el borde
        };

        const eventDataForBackend = {
            title: title,
            start: start,
            end: end, // Enviar también el end al backend si tu DB lo soporta
            description: description
            // El color se maneja en el frontend con FullCalendar, no se envía al backend si tu tabla 'evento' no lo guarda.
        };

        fetch('add-event.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(eventDataForBackend),
        })
        .then(response => {
            if (!response.ok) {
                // Leer la respuesta como texto para depuración si no es JSON o hay un error HTTP
                return response.text().then(text => {
                    throw new Error(`Error del servidor: ${response.status} ${response.statusText}. Respuesta: ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.id) { // Asegurarse que data.id existe
                newEventDataForFullCalendar.id = data.id; // Usa el ID de la base de datos para el evento en FullCalendar
                calendar.addEvent(newEventDataForFullCalendar);

                const savedEvents = getSavedEvents();
                // Asegurarse de no duplicar si el evento ya fue añadido por la carga inicial,
                // aunque con el ID de la BD debería ser único.
                if (!savedEvents.find(ev => ev.id === newEventDataForFullCalendar.id)) {
                    savedEvents.push(newEventDataForFullCalendar);
                    saveEvents(savedEvents);
                }

                document.getElementById('new-event-modal').style.display = 'none';
                this.reset(); // Limpiar el formulario

                // ******************************************************************
                // REEMPLAZO DE LA ALERTA POR LA NOTIFICACIÓN TOAST
                // ******************************************************************
                if (toast && progress) { // Asegurarse de que los elementos del toast existen
                    // Actualizar el texto del mensaje si es necesario (el tuyo ya dice "Evento añadido")
                    const messageText1 = document.querySelector(".toast-content .message .text.text-1");
                    if (messageText1) {
                        messageText1.textContent = "Evento añadido"; // O "Evento guardado exitosamente."
                    }

                    toast.classList.add("active");
                    progress.classList.add("active");

                    // Ocultar la notificación después de 5 segundos (matching progress bar duration)
                    setTimeout(() => {
                        toast.classList.remove("active");
                        // Opcional: remover la clase active del progreso con un pequeño delay extra
                        setTimeout(() => {
                            progress.classList.remove("active");
                        }, 300);
                    }, 5000);
                } else {
                    // Fallback a la alerta si el toast no se encuentra (en caso de error en el HTML)
                    alert(data.message || 'Evento guardado exitosamente.');
                }
                // ******************************************************************
                // FIN DEL REEMPLAZO
                // ******************************************************************

            } else {
                alert('Error al guardar el evento: ' + (data.message || 'Respuesta de error no especificada o ID faltante.'));
                console.error('Error from backend:', data);
            }
        })
        .catch(error => {
            console.error('Error en la solicitud AJAX o al procesar la respuesta:', error);
            alert('Error de conexión o respuesta inválida al guardar el evento. Revise la consola del navegador para más detalles.\nDetalle: ' + error.message);
        });
    });

    // Cerrar modales (mantén esta lógica, aplica a todos los elementos con clase 'close')
    document.querySelectorAll('.modal .close').forEach(button => { // Más específico ".modal .close"
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    });

    // Mini calendario
    const miniCalendarEl = document.getElementById('mini-calendar');
    let miniCalendarInstance = null; // Para evitar re-renderizar si no es necesario

    if (miniCalendarEl) {
        miniCalendarInstance = new FullCalendar.Calendar(miniCalendarEl, {
            locale: 'es',
            initialView: 'dayGridMonth',
            headerToolbar: {
                start: 'title',
                center: '',
                end: 'prev,next' // Simplificado, 'today' puede ser redundante
            },
            height: 'auto', // Para que se ajuste al contenido
            dateClick: function(info) {
                calendar.gotoDate(info.date); // El calendario principal va a la fecha clickeada
                updateMonthYear(calendar);
            },
            datesSet: function(dateInfo) { // Cuando la vista del mini calendario cambia
                // Esto podría causar un bucle si no se maneja con cuidado.
                // calendar.gotoDate(miniCalendarInstance.getDate());
                // updateMonthYear(calendar);
            }
        });
        miniCalendarInstance.render();
    }

    // Sincronización entre calendarios (simplificada para evitar bucles)
    let syncing = false;
    calendar.on('datesSet', function(dateInfo) { // Cuando el calendario principal cambia de vista/fecha
        if (!syncing && miniCalendarInstance) {
            syncing = true;
            miniCalendarInstance.gotoDate(calendar.getDate());
            updateMonthYear(calendar); // Asegurarse que el título principal se actualiza
            setTimeout(() => syncing = false, 50); // Pequeño delay para evitar bucles
        }
    });

    if (miniCalendarInstance) {
        miniCalendarInstance.on('dateClick', function(info) { // Cuando se hace clic en una fecha del mini calendario
             if (!syncing) {
                syncing = true;
                calendar.gotoDate(info.date);
                // `datesSet` del calendario principal se encargará de `updateMonthYear`
                setTimeout(() => syncing = false, 50);
            }
        });
    }

    // Para la librería de color (si la usas, asegúrate de incluirla)
    // Ejemplo de función simple para brillo (0 oscuro, 1 claro)
    function getBrightness(hexColor) {
        if (!hexColor || hexColor.length < 4) return 0.5; // Default si el color es inválido
        hexColor = hexColor.replace('#', '');
        let r, g, b;
        if (hexColor.length === 3) {
            r = parseInt(hexColor[0] + hexColor[0], 16);
            g = parseInt(hexColor[1] + hexColor[1], 16);
            b = parseInt(hexColor[2] + hexColor[2], 16);
        } else if (hexColor.length === 6) {
            r = parseInt(hexColor.substring(0, 2), 16);
            g = parseInt(hexColor.substring(2, 4), 16);
            b = parseInt(hexColor.substring(4, 6), 16);
        } else {
            return 0.5; // Default
        }
        // Fórmula de luminosidad relativa (YIQ)
        return (0.299 * r + 0.587 * g + 0.114 * b) / 255;
    }

    // FullCalendar eventDidMount para ajustar color de texto basado en el fondo
    // (reemplaza la función simple de chroma con la de arriba o incluye Chroma.js)
    calendar.setOption('eventDidMount', function(info) {
        if (info.event.backgroundColor) {
            info.el.style.backgroundColor = info.event.backgroundColor;
            info.el.style.borderLeft = `4px solid ${info.event.backgroundColor}`;
        }
        const bgColor = info.event.backgroundColor || '#3eb489';
        const brightness = getBrightness(bgColor);
        info.el.style.color = brightness < 0.55 ? 'white' : '#333'; // Ajusta el umbral 0.55 según sea necesario
    });

    // Asegurar que la vista activa se marque al cargar
    setActiveView(calendar.view.type.replace(/dayGrid|timeGrid/, '').toLowerCase() || 'month');

});