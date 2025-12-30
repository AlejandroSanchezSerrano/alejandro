// --- CONFIGURACIÓN ---
const API_BASE_URL = 'https://alejandrodev.es/api'; 

const year = 2026;
let currentMonth = 0; 
const months = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
let habitsData = {};

document.addEventListener('DOMContentLoaded', async () => {
    initAutoDate();
    
    // Verificar si estamos logueados al entrar
    await checkSession();

    // Listeners Login
    document.getElementById('authForm').addEventListener('submit', handleAuth);
    document.getElementById('toggleAuthMode').addEventListener('click', toggleAuthMode);
    
    // Listeners Dashboard
    document.getElementById('btnLogout').addEventListener('click', logout);
    document.getElementById('btnLogoutMobile').addEventListener('click', logout);
    
    const btnNew = document.getElementById('btnNewHabit');
    if(btnNew) btnNew.addEventListener('click', addHabitPrompt);
});

// --- AUTENTICACIÓN Y SESIÓN ---

async function checkSession() {
    try {
        const formData = new FormData();
        formData.append('action', 'check_session');
        
        // credentials: 'include' es VITAL para que viajen las cookies entre dominios
        const res = await fetch(`${API_BASE_URL}/auth.php`, { 
            method: 'POST', 
            body: formData,
            credentials: 'include' 
        });
        const data = await res.json();
        
        if(data.logged_in) {
            showDashboard(data.user);
        } else {
            showLogin();
        }
    } catch(e) {
        console.error("Error conexión:", e);
        showLogin();
    }
}

async function handleAuth(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const msg = document.getElementById('msg');
    msg.innerText = "Conectando...";

    try {
        const res = await fetch(`${API_BASE_URL}/auth.php`, { 
            method: 'POST', 
            body: formData,
            credentials: 'include'
        });
        const data = await res.json();
        
        if(data.success) {
            msg.innerText = "";
            showDashboard(data.user || formData.get('username'));
        } else {
            msg.innerText = data.message;
        }
    } catch(e) {
        msg.innerText = "Error de conexión con el servidor.";
    }
}

async function logout() {
    const formData = new FormData();
    formData.append('action', 'logout');
    await fetch(`${API_BASE_URL}/auth.php`, { method: 'POST', body: formData, credentials: 'include' });
    showLogin();
}

// --- GESTIÓN DE VISTAS (SPA) ---

function showDashboard(username) {
    document.getElementById('loginView').style.display = 'none';
    document.getElementById('dashboardView').style.display = 'block';
    
    document.getElementById('usernameDisplay').innerText = `HOLA, ${username.toUpperCase()}`;
    document.getElementById('usernameDisplayMobile').innerText = `HOLA, ${username.toUpperCase()}`;
    
    loadHabits(); // Cargar datos
}

function showLogin() {
    document.getElementById('dashboardView').style.display = 'none';
    document.getElementById('loginView').style.display = 'flex';
    document.getElementById('authForm').reset();
}

let isLoginMode = true;
function toggleAuthMode() {
    isLoginMode = !isLoginMode;
    const btnSubmit = document.querySelector('#authForm button[type="submit"]');
    const actionInput = document.getElementById('actionInput');
    const toggleBtn = document.getElementById('toggleAuthMode');
    
    if(isLoginMode) {
        btnSubmit.innerText = "ENTRAR";
        btnSubmit.style.background = "#4ade80";
        actionInput.value = "login";
        toggleBtn.innerText = "REGISTRARSE";
    } else {
        btnSubmit.innerText = "CREAR CUENTA";
        btnSubmit.style.background = "#60a5fa";
        actionInput.value = "register";
        toggleBtn.innerText = "VOLVER";
    }
}

// --- LÓGICA DE LA APP (DASHBOARD) ---

function initAutoDate() {
    const title = document.getElementById('monthTitle');
    const now = new Date();
    if (now.getFullYear() === 2026) currentMonth = now.getMonth();
    else if (now.getFullYear() < 2026) currentMonth = 0;
    else currentMonth = 11;

    if(title) title.innerText = `${months[currentMonth]} ${year}`;
}

async function loadHabits() {
    try {
        const formData = new FormData();
        formData.append('action', 'load');
        const res = await fetch(`${API_BASE_URL}/api.php`, { 
            method: 'POST', 
            body: formData,
            credentials: 'include' 
        });
        habitsData = await res.json();
        
        renderCalendar();
        renderVisualProgress();
        renderDailyView();
    } catch (e) { console.error("Error cargando hábitos", e); }
}

function getDaysInMonth(month, year) {
    return new Date(year, month + 1, 0).getDate();
}

// ... (Resto de funciones de renderizado: renderCalendar, renderVisualProgress, renderDailyView)
// ... (Copiar exactamente las mismas funciones de tu script.js anterior PERO...)
// IMPORTANTE: En toggleCheck y addHabitPrompt cambiar fetch('api.php'...) por fetch(`${API_BASE_URL}/api.php`...) 
// y añadir credentials: 'include'.

// Aquí te dejo el ejemplo de cómo debe quedar toggleCheck y addHabitPrompt modificados:

async function toggleCheck(habitId, dateStr) {
    const formData = new FormData();
    formData.append('action', 'toggle_check');
    formData.append('habitId', habitId);
    formData.append('date', dateStr);

    try {
        // Optimistic UI update (actualiza visualmente antes de esperar al servidor)
        const habit = habitsData[habitId];
        if(!habit.checks) habit.checks = [];
        if(habit.checks.includes(dateStr)) {
            habit.checks = habit.checks.filter(d => d !== dateStr);
        } else {
            habit.checks.push(dateStr);
        }
        renderVisualProgress();
        if(window.innerWidth > 768) renderCalendar();

        await fetch(`${API_BASE_URL}/api.php`, { 
            method: 'POST', 
            body: formData, 
            credentials: 'include' 
        });
    } catch(e) { console.error("Error guardando:", e); }
}

async function addHabitPrompt() {
    const name = prompt("¿Qué hábito quieres forjar?");
    if(!name) return;
    const category = prompt("Categoría:", "General");
    const colors = ['#4ade80', '#60a5fa', '#f472b6', '#fbbf24', '#a78bfa', '#f87171', '#2dd4bf'];
    const color = colors[Math.floor(Math.random() * colors.length)];

    const formData = new FormData();
    formData.append('action', 'add_habit');
    formData.append('name', name);
    formData.append('category', category);
    formData.append('color', color);

    try {
        const res = await fetch(`${API_BASE_URL}/api.php`, { 
            method: 'POST', 
            body: formData,
            credentials: 'include'
        });
        const data = await res.json();
        if(data.success) loadHabits();
    } catch(e) { console.error(e); }
}

function toggleMenu() {
    document.getElementById('mobileMenu').classList.toggle('active');
}
