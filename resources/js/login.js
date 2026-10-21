import { api, auth, ApiError, clearFieldErrors, showFieldErrors, setAlert } from './api';

if (auth.isLoggedIn()) {
    window.location.href = '/';
}

const form = document.getElementById('loginForm');
const formError = document.getElementById('formError');
const button = document.getElementById('loginButton');

form.addEventListener('submit', async (event) => {
    event.preventDefault();

    clearFieldErrors(form);
    setAlert(formError, '');

    const data = new FormData(form);
    button.disabled = true;

    try {
        const result = await api('/login', {
            method: 'POST',
            body: { email: data.get('email'), password: data.get('password') },
        });

        auth.setToken(result.data.token);
        window.location.href = '/';
    } catch (error) {
        if (error instanceof ApiError && Object.keys(error.errors).length) {
            const unmatched = showFieldErrors(form, error.errors);
            if (unmatched.length) setAlert(formError, unmatched.join(' '));
        } else {
            setAlert(formError, error.message);
        }
    } finally {
        button.disabled = false;
    }
});
