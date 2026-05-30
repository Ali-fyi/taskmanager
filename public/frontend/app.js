/**
 * app.js — Shared API logic across all pages
 *
 * Usage in a page:
 *   <script src="app.js"></script>
 *   then: requireAuth(), api.tasks(), api.createTask({…}), etc.
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
 * Redirects to login.html if there is no token.
 * Call at the top of every protected page.
 */
function requireAuth() {
    if (!getToken()) {
        window.location.href = 'login.html';
        return false;
    }
    return true;
}

/**
 * Central fetch wrapper.
 * Automatically adds the Bearer token.
 * Redirects to login.html on a 401.
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
        throw new Error('Unable to reach the API. Check that taskmanager.test is accessible.');
    }

    // Token expired or revoked → redirect to login
    if (res.status === 401) {
        removeToken();
        window.location.href = 'login.html';
        throw new Error('Session expired.');
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

// ── UI helpers ────────────────────────────────────────────────────

/** Displays an error message in an element (clears it if msg is empty). */
function showError(elId, msg) {
    const el = document.getElementById(elId);
    if (!el) return;
    el.textContent = msg;
    el.classList.toggle('hidden', !msg);
}

/** Formats an ISO date as dd/mm/yyyy. */
function formatDate(iso) {
    if (!iso) return null;
    const [y, m, d] = iso.split('-');
    return d + '/' + m + '/' + y;
}

/** Returns true if the date (string yyyy-mm-dd) is in the past. */
function isOverdue(iso) {
    if (!iso) return false;
    return new Date(iso) < new Date(new Date().toDateString());
}
