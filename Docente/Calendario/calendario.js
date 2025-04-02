// Configuración del cliente de Google Calendar API
const CLIENT_ID = 'TU_CLIENT_ID';
const API_KEY = 'TU_API_KEY';
const DISCOVERY_DOCS = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest"];
const SCOPES = "https://www.googleapis.com/auth/calendar.readonly";

let tokenClient;
let gapiInited = false;
let gisInited = false;

document.addEventListener('DOMContentLoaded', initializeApp);

function initializeApp() {
    // Cargar el script de la API de Google
    const script1 = document.createElement('script');
    script1.src = 'https://apis.google.com/js/api.js';
    script1.onload = gapiLoaded;
    document.body.appendChild(script1);
    
    const script2 = document.createElement('script');
    script2.src = 'https://accounts.google.com/gsi/client';
    script2.onload = gisLoaded;
    document.body.appendChild(script2);
    
    // Agregar botón de autorización
    const actionBar = document.querySelector('.action-bar');
    const authButton = document.createElement('button');
    authButton.textContent = 'Conectar con Google Calendar';
    authButton.className = 'google-auth-btn';
    authButton.onclick = handleAuthClick;
    actionBar.appendChild(authButton);
}

function gapiLoaded() {
    gapi.load('client', initializeGapiClient);
}

async function initializeGapiClient() {
    await gapi.client.init({
        apiKey: API_KEY,
        discoveryDocs: DISCOVERY_DOCS,
    });
    gapiInited = true;
    maybeEnableButtons();
}

function gisLoaded() {
    tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: CLIENT_ID,
        scope: SCOPES,
        callback: '', // definido después en handleAuthClick
    });
    gisInited = true;
    maybeEnableButtons();
}

function maybeEnableButtons() {
    if (gapiInited && gisInited) {
        document.querySelector('.google-auth-btn').disabled = false;
    }
}

function handleAuthClick() {
    tokenClient.callback = async (resp) => {
        if (resp.error !== undefined) {
            throw (resp);
        }
        await listUpcomingEvents();
    };

    if (gapi.client.getToken() === null) {
        tokenClient.requestAccessToken({prompt: 'consent'});
    } else {
        tokenClient.requestAccessToken({prompt: ''});
    }
}

async function listUpcomingEvents() {
    try {
        const response = await gapi.client.calendar.events.list({
            'calendarId': 'primary',
            'timeMin': (new Date()).toISOString(),
            'showDeleted': false,
            'singleEvents': true,
            'maxResults': 30,
            'orderBy': 'startTime'
        });

        const events = response.result.items;
        populateCalendar(events);
        
    } catch (err) {
        console.error('Error al obtener eventos:', err);
        alert('Error al obtener eventos de Google Calendar');
    }
}

function populateCalendar(events) {
    // Limpiar eventos existentes
    document.querySelectorAll('.event').forEach(el => el.remove());
    
    // Agregar eventos al calendario
    events.forEach(event => {
        const startDate = new Date(event.start.dateTime || event.start.date);
        const day = startDate.getDate();
        const month = startDate.getMonth();
        
        // Buscar la celda correspondiente a la fecha del evento
        // Esto es simplificado - necesitarás ajustarlo para que funcione con tu estructura de calendario
        const cells = document.querySelectorAll('.day-cell');
        cells.forEach(cell => {
            const dayNumber = parseInt(cell.querySelector('.day-number').textContent);
            
            // Comprobar si esta celda corresponde al día del evento
            // Nota: Esta es una implementación básica, deberás adaptar la lógica a tu calendario
            if (dayNumber === day && currentMonth === month) {
                // Crear elemento del evento
                const eventElement = document.createElement('div');
                eventElement.className = 'event';
                eventElement.textContent = event.summary;
                eventElement.title = event.summary;
                
                // Añadir información adicional como atributos de datos
                eventElement.dataset.eventId = event.id;
                eventElement.dataset.startTime = event.start.dateTime || event.start.date;
                eventElement.dataset.endTime = event.end.dateTime || event.end.date;
                
                // Añadir evento para mostrar detalles al hacer clic
                eventElement.addEventListener('click', () => showEventDetails(event));
                
                // Añadir a la celda del día
                cell.appendChild(eventElement);
            }
        });
    });
}

function showEventDetails(event) {
    // Implementar un modal o diálogo para mostrar detalles del evento
    console.log('Detalles del evento:', event);
    
    // Ejemplo básico de modal (puedes implementar uno más sofisticado)
    const modal = document.createElement('div');
    modal.className = 'event-modal';
    
    const modalContent = document.createElement('div');
    modalContent.className = 'event-modal-content';
    
    // Crear contenido del modal
    const title = document.createElement('h3');
    title.textContent = event.summary;
    
    const time = document.createElement('p');
    const startTime = new Date(event.start.dateTime || event.start.date);
    const endTime = new Date(event.end.dateTime || event.end.date);
    time.textContent = `${formatDate(startTime)} - ${formatDate(endTime)}`;
    
    const description = document.createElement('p');
    description.textContent = event.description || 'Sin descripción';
    
    const closeBtn = document.createElement('button');
    closeBtn.textContent = 'Cerrar';
    closeBtn.onclick = () => document.body.removeChild(modal);
    
    // Ensamblar el modal
    modalContent.appendChild(title);
    modalContent.appendChild(time);
    modalContent.appendChild(description);
    modalContent.appendChild(closeBtn);
    modal.appendChild(modalContent);
    
    // Añadir al cuerpo del documento
    document.body.appendChild(modal);
}

function formatDate(date) {
    return `${date.toLocaleDateString()} ${date.toLocaleTimeString()}`;
}

// Variable para rastrear el mes y año actual en el calendario
let currentMonth = new Date().getMonth();
let currentYear = new Date().getFullYear();

// Función para actualizar el mes en pantalla
function updateCalendarMonth(year, month) {
    currentMonth = month;
    currentYear = year;
    
    // Actualizar título del calendario
    const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    document.querySelector('.month-year').textContent = `${monthNames[month]} ${year}`;
    document.querySelector('.main-calendar h2').textContent = `${monthNames[month]} ${year}`;
    
    // Aquí implementarías la lógica para actualizar la vista del calendario
    // con los días correspondientes al mes y año especificados
    
    // Luego cargar eventos de Google Calendar para este mes
    if (gapi.client && gapi.client.getToken() !== null) {
        // Crear fechas para el primer y último día del mes
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        
        fetchEventsForMonth(firstDay, lastDay);
    }
}

async function fetchEventsForMonth(startDate, endDate) {
    try {
        const response = await gapi.client.calendar.events.list({
            'calendarId': 'primary',
            'timeMin': startDate.toISOString(),
            'timeMax': endDate.toISOString(),
            'showDeleted': false,
            'singleEvents': true,
            'maxResults': 100,
            'orderBy': 'startTime'
        });

        const events = response.result.items;
        populateCalendar(events);
        
    } catch (err) {
        console.error('Error al obtener eventos:', err);
    }
}

// Inicializar botones de navegación
document.addEventListener('DOMContentLoaded', () => {
    // Botón Hoy
    const todayBtn = document.querySelector('.today-btn');
    todayBtn.addEventListener('click', () => {
        const today = new Date();
        updateCalendarMonth(today.getFullYear(), today.getMonth());
    });
    
    // Botones de navegación anterior y siguiente
    const prevBtn = document.querySelector('.nav-btn:first-child');
    const nextBtn = document.querySelector('.nav-btn:last-child');
    
    prevBtn.addEventListener('click', () => {
        let month = currentMonth - 1;
        let year = currentYear;
        if (month < 0) {
            month = 11;
            year--;
        }
        updateCalendarMonth(year, month);
    });
    
    nextBtn.addEventListener('click', () => {
        let month = currentMonth + 1;
        let year = currentYear;
        if (month > 11) {
            month = 0;
            year++;
        }
        updateCalendarMonth(year, month);
    });
});