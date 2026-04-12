import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
const currentHost = window.location.hostname;
const configuredHost = (import.meta.env.VITE_REVERB_HOST ?? '').trim();
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT ?? 8080);
const pageScheme = window.location.protocol === 'https:' ? 'https' : 'http';
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? pageScheme;
const shouldPreferCurrentHost = !configuredHost
    || configuredHost === '0.0.0.0'
    || (configuredHost !== currentHost && currentHost !== 'localhost' && currentHost !== '127.0.0.1');
const wsHost = shouldPreferCurrentHost ? currentHost : configuredHost;

window.__realtimeConnected = false;
window.isRealtimeConnected = () => window.__realtimeConnected === true;
window.waitForRealtimeConnection = (timeout = 2500) => new Promise((resolve) => {
    if (!window.Echo?.connector?.pusher?.connection) {
        resolve(false);
        return;
    }

    if (window.isRealtimeConnected()) {
        resolve(true);
        return;
    }

    let settled = false;

    const cleanup = () => {
        window.removeEventListener('realtime:connected', handleConnected);
        window.removeEventListener('realtime:failed', handleFailed);
    };

    const finish = (value) => {
        if (settled) {
            return;
        }

        settled = true;
        cleanup();
        resolve(value);
    };

    const handleConnected = () => finish(true);
    const handleFailed = () => finish(false);

    window.addEventListener('realtime:connected', handleConnected, { once: true });
    window.addEventListener('realtime:failed', handleFailed, { once: true });

    setTimeout(() => finish(window.isRealtimeConnected()), timeout);
});

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: csrfToken ? {
            'X-CSRF-TOKEN': csrfToken,
        } : {},
    },
});

const connection = window.Echo.connector?.pusher?.connection;

if (connection) {
    connection.bind('connected', () => {
        window.__realtimeConnected = true;
        window.dispatchEvent(new CustomEvent('realtime:connected'));
    });

    ['disconnected', 'unavailable', 'failed'].forEach((eventName) => {
        connection.bind(eventName, () => {
            window.__realtimeConnected = false;
            window.dispatchEvent(new CustomEvent('realtime:failed', {
                detail: { state: eventName },
            }));
        });
    });

    connection.bind('error', () => {
        window.__realtimeConnected = false;
    });
}
