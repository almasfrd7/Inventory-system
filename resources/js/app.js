import './bootstrap';
import { api, auth } from './api';

// Logout button in the navbar (only visible when logged in).
const logoutButton = document.getElementById('logoutButton');

if (logoutButton && auth.isLoggedIn()) {
    logoutButton.classList.remove('d-none');

    logoutButton.addEventListener('click', async () => {
        try {
            await api('/logout', { method: 'POST' });
        } catch {
            // Even if the call fails, remove the local token.
        }

        auth.clear();
        window.location.href = '/login';
    });
}
