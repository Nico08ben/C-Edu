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

    // Inicializar FullCalendar
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        locale: 'es',
        initialView: 'dayGridMonth',
        headerToolbar: false, // Ocultamos la barra de herramientas nativa
        firstDay: 1, // Lunes como primer día de la semana
        events: getSavedEvents(),
        eventDisplay: 'block',
        eventColor: '#3eb489', // Color por defecto
        eventTextColor: '#333',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        },
        dateClick: function(info) {
            // Abrir modal para nuevo evento al hacer clic en una fecha
            const startDate = new Date(info.date);
            startDate.setHours(12, 0); // Hora por defecto: 12:00 PM
            
            document.getElementById('event-start').value = formatDateTimeLocal(startDate);
            document.getElementById('new-event-modal').style.display = 'block';
        },
        eventClick: function(info) {
            // Mostrar detalles del evento al hacer clic en él
            const event = info.event;
            document.getElementById('event-title').textContent = event.title;
            
            // Formatear fecha y hora
            let dateStr = '';
            if (event.allDay) {
                dateStr = event.start.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            } else {
                dateStr = event.start.toLocaleString('es-ES', { 
                    weekday: 'long', 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric', 
                    hour: '2-digit', 
                    minute: '2-digit' 
                });
                
                if (event.end) {
                    dateStr += ' - ' + event.end.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
                }
            }
            
            document.getElementById('event-date').innerHTML = `<span>${dateStr}</span>`;
            document.getElementById('event-description').innerHTML = `<span>${event.extendedProps.description || 'Sin descripción'}</span>`;
            
            // Configurar botón de eliminar
            document.getElementById('delete-event').onclick = function() {
                if (confirm('¿Estás seguro de eliminar este evento?')) {
                    const savedEvents = getSavedEvents();
                    const updatedEvents = savedEvents.filter(e => e.id !== event.id);
                    saveEvents(updatedEvents);
                    
                    event.remove();
                    document.getElementById('event-modal').style.display = 'none';
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
        }
    });

    calendar.render();
    updateMonthYear(calendar);

    // Función para formatear fecha para input datetime-local
    function formatDateTimeLocal(date) {
        const d = new Date(date);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    }

    // Actualizar el mes y año mostrado
    function updateMonthYear(calendar) {
        const view = calendar.view;
        const title = view.title;
        document.getElementById('month-year').textContent = title;
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
    });

    document.getElementById('week-view').addEventListener('click', function() {
        calendar.changeView('timeGridWeek');
        setActiveView('week');
    });

    document.getElementById('month-view').addEventListener('click', function() {
        calendar.changeView('dayGridMonth');
        setActiveView('month');
    });

    // Función para marcar la vista activa
    function setActiveView(view) {
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById(`${view}-view`).classList.add('active');
    }

    // Configurar modal de nuevo evento
    document.getElementById('new-event-btn').addEventListener('click', function() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        
        document.getElementById('event-start').value = formatDateTimeLocal(now);
        document.getElementById('event-end').value = '';
        document.getElementById('event-name').value = '';
        document.getElementById('event-description').value = '';
        document.getElementById('event-color').value = '#3eb489';
        
        document.getElementById('new-event-modal').style.display = 'block';
    });

    // Manejar envío del formulario de nuevo evento
    document.getElementById('event-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const title = document.getElementById('event-name').value;
        const start = document.getElementById('event-start').value;
        const end = document.getElementById('event-end').value;
        const description = document.getElementById('event-description').value;
        const color = document.getElementById('event-color').value;
        
        const newEvent = {
            id: 'event_' + Date.now(),
            title: title,
            start: start,
            end: end || undefined,
            description: description,
            backgroundColor: color,
            borderColor: color
        };
        
        // Guardar el evento
        const savedEvents = getSavedEvents();
        savedEvents.push(newEvent);
        saveEvents(savedEvents);
        
        // Añadir al calendario
        calendar.addEvent(newEvent);
        
        // Cerrar modal y limpiar formulario
        document.getElementById('new-event-modal').style.display = 'none';
        this.reset();
    });

    // Cerrar modales
    document.querySelectorAll('.close').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.modal').style.display = 'none';
        });
    });

    // Cerrar modal al hacer clic fuera del contenido
    window.addEventListener('click', function(event) {
        if (event.target.className === 'modal') {
            event.target.style.display = 'none';
        }
    });

    // Mini calendario
    const miniCalendar = new FullCalendar.Calendar(document.getElementById('mini-calendar'), {
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
            updateMonthYear(calendar);
        },
        datesSet: function() {
            // Sincronizar el mini calendario con el principal
            calendar.gotoDate(miniCalendar.getDate());
            updateMonthYear(calendar);
        }
    });

    miniCalendar.render();

    let syncing = false;

calendar.on('datesSet', function() {
    if (!syncing) {
        syncing = true;
        miniCalendar.gotoDate(calendar.getDate());
        setTimeout(() => syncing = false, 10);
    }
});

miniCalendar.setOption('datesSet', function() {
    if (!syncing) {
        syncing = true;
        calendar.gotoDate(miniCalendar.getDate());
        updateMonthYear(calendar);
        setTimeout(() => syncing = false, 10);
    }
});


    // Cargar eventos iniciales si no hay ninguno guardado
    if (getSavedEvents().length === 0) {
        const today = new Date();
        
        
        sampleEvents.forEach(event => calendar.addEvent(event));
        saveEvents(sampleEvents);
    }
});