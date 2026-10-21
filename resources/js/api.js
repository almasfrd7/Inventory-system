/*
 * Shared helpers: token storage, fetch wrapper, form-error helpers.
 */

const TOKEN_KEY = 'inventory_token';

export const auth = {
    getToken: () => localStorage.getItem(TOKEN_KEY),
    setToken: (token) => localStorage.setItem(TOKEN_KEY, token),
    clear: () => localStorage.removeItem(TOKEN_KEY),
    isLoggedIn: () => !!localStorage.getItem(TOKEN_KEY),
};

export class ApiError extends Error {
    constructor(message, status = 0, errors = {}) {
        super(message);
        this.status = status;
        this.errors = errors;
    }
}

/** Redirect to the login page when there is no token. */
export function requireLogin() {
    if (!auth.isLoggedIn()) {
        window.location.href = '/login';
        return false;
    }
    return true;
}

/**
 * api('/products', { method: 'POST', body: {...}, params: {page: 2} })
 * Returns the parsed JSON, or throws ApiError.
 */
export async function api(path, { method = 'GET', body, params } = {}) {
    const url = new URL('/api' + path, window.location.origin);

    if (params) {
        Object.entries(params).forEach(([key, value]) => {
            if (value !== '' && value !== null && value !== undefined) {
                url.searchParams.set(key, value);
            }
        });
    }

    const headers = { Accept: 'application/json' };
    const token = auth.getToken();

    if (token) headers.Authorization = `Bearer ${token}`;
    if (body !== undefined) headers['Content-Type'] = 'application/json';

    let response;

    try {
        response = await fetch(url, {
            method,
            headers,
            body: body !== undefined ? JSON.stringify(body) : undefined,
        });
    } catch {
        throw new ApiError('Cannot reach the server. Check your connection.');
    }

    let data = null;

    try {
        data = await response.json();
    } catch {
        // Non-JSON response; handled below.
    }

    // Expired / invalid token: back to the login page.
    if (response.status === 401 && path !== '/login') {
        auth.clear();
        window.location.href = '/login';
        throw new ApiError('Your session has expired.', 401);
    }

    if (!response.ok) {
        throw new ApiError(data?.message || 'Request failed.', response.status, data?.errors || {});
    }

    return data;
}

export function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

/** Remove red borders and messages from a form. */
export function clearFieldErrors(form) {
    form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach((el) => (el.textContent = ''));
}

/**
 * Show each error under its input (matched by the input's name attribute).
 * Returns the messages that had no matching input.
 */
export function showFieldErrors(form, errors = {}) {
    const unmatched = [];

    Object.entries(errors).forEach(([field, messages]) => {
        const input = form.querySelector(`[name="${field}"]`);

        if (!input) {
            unmatched.push(...messages);
            return;
        }

        input.classList.add('is-invalid');

        const feedback = input.parentElement.querySelector('.invalid-feedback');
        if (feedback) feedback.textContent = messages[0];
    });

    return unmatched;
}

/** Show a message in an alert box (pass '' to hide it). */
export function setAlert(element, text) {
    element.textContent = text;
    element.classList.toggle('d-none', !text);
}
