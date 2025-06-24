import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
        },
    },
});

// Debug Echo connection
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('🟢 Echo: Successfully connected to Reverb');
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('🔴 Echo: Disconnected from Reverb');
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.error('❌ Echo: Connection error:', error);
});

console.log('🚀 Echo initialized with config:', {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    state: window.Echo.connector.pusher.connection.state
});
