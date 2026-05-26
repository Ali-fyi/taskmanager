/**
 * app.js — Logique API partagée entre toutes les pages
 *
 * Utilisation dans une page :
 *   <script src="app.js"></script>
 *   puis : requireAuth(), api.tasks(), api.createTask({…}), etc.
 */

const API_BASE  = 'http://taskmanager.test/api';
const TOKEN_KEY = 'taskmanager_token';
const USER_KEY  = 'taskmanager_user';

// ── Token & User ──────────────────────────────────────────────────
function getToken()    { return localStorage.getItem(TOKEN_KEY); }
function setToken(t)   { localStorage.setItem(TOKEN_KEY, t); }
function removeToken() { localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(USER_KEY); }
function getUser()     { try { return JSON.parse(localStorage.getItem(USER_KEY)); } catch { return null; } }
function setUser(u)    { localStorage.setItem(USER_KEY, JSON.stringify(u)); }

/**
 * Redirige vers login.html si pas de token.
 * À appeler en haut de chaque page protégée.
 */
function requireAuth() {
    if (!getToken()) {
        window.location.href = 'login.html';
        return false;
    }
    return true;
}

/**
 * Wrapper fetch central.
 * Ajoute automatiquement le Bearer token.
 * Redirige vers login.html en cas de 401.
 */
async function apiFetch(method, path, body = null) {
    const headers = {
        'Accept':       'application/json',
        'Content-Type': 'application/json',
    };

    const token = getToken();
    if (token) headers['Authorization'] = 'Bearer ' + token;

    const options = { method, headers };
    if (body) options.body = JSON.stringify(body);

    let res;
    try {
        res = await fetch(API_BASE + path, options);
    } catch (err) {
        throw new Error('Impossible de contacter l\'API. Vérifiez que taskmanager.test est accessible.');
    }

    // Token expiré ou révoqué → redirect login
    if (res.status === 401) {
        removeToken();
        window.location.href = 'login.html';
        throw new Error('Session expirée.');
    }

    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch { data = { message: text }; }

    return { ok: res.ok, status: res.status, data };
}

// ── API methods ───────────────────────────────────────────────────
const api = {
    login:      (email, password) => apiFetch('POST', '/login',           { email, password }),
    logout:     ()                => apiFetch('POST', '/logout'),
    tasks:      ()                => apiFetch('GET',  '/tasks'),
    getTask:    (id)              => apiFetch('GET',  '/tasks/' + id),
    createTask: (body)            => apiFetch('POST', '/tasks',            body),
    updateTask: (id, body)        => apiFetch('PUT',  '/tasks/' + id,      body),
    deleteTask: (id)              => apiFetch('DELETE', '/tasks/' + id),
    projects:   ()                => apiFetch('GET',  '/projects'),
};

// ── Helpers UI ────────────────────────────────────────────────────

/** Affiche un message d'erreur dans un élément (le vide si msg est vide). */
function showError(elId, msg) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent = msg;
    el.classList.toggle('hidden', !msg);
}

/** Formate une date ISO en dd/mm/yyyy. */
function formatDate(iso) {
    if (!iso) return null;
    const [y, m, d] = iso.split('-');
    return d + '/' + m + '/' + y;
}

/** Renvoie true si la date (string yyyy-mm-dd) est passée. */
function isOverdue(iso) {
    if (!iso) return false;
    return new Date(iso) < new Date(new Date().toDateString());
}
