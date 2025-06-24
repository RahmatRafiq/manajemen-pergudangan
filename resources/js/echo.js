import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

console.log('🚀 LIVE UPDATE: Initializing Echo for live stock alerts...');

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

// Debug Echo connection - PRIORITAS LIVE UPDATE WEBSOCKET
window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('🟢 LIVE UPDATE: Successfully connected to Reverb WebSocket');
    console.log('🎯 LIVE UPDATE: Ready to receive real-time stock alerts');
    
    // Test subscription ke public channel
    setTimeout(() => {
        console.log('🔗 LIVE UPDATE: Testing public channel subscription...');
        const testChannel = window.Echo.channel('stock-alerts-public');
        console.log('📺 LIVE UPDATE: Subscribed to stock-alerts-public channel');
        
        // Setup listener test
        testChannel.listen('stock.level.changed', () => {
            console.log('🎯 LIVE UPDATE: Test listener attached successfully');
        });
    }, 1000);
});

window.Echo.connector.pusher.connection.bind('disconnected', () => {
    console.log('🔴 LIVE UPDATE: Disconnected from Reverb WebSocket');
});

window.Echo.connector.pusher.connection.bind('error', (error) => {
    console.error('❌ LIVE UPDATE: WebSocket connection error:', error);
});

window.Echo.connector.pusher.connection.bind('state_change', (states) => {
    console.log('🔄 LIVE UPDATE: Connection state changed:', states.previous, '→', states.current);
});

console.log('🚀 LIVE UPDATE: Echo initialized with config:', {
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    currentState: window.Echo.connector.pusher.connection.state
});

// Test koneksi setelah 2 detik
setTimeout(() => {
    const state = window.Echo.connector.pusher.connection.state;
    console.log('📊 LIVE UPDATE: Connection status after 2s:', state);
    
    if (state === 'connected') {
        console.log('✅ LIVE UPDATE: WebSocket ready for real-time updates!');
    } else {
        console.warn('⚠️ LIVE UPDATE: WebSocket not connected yet, state:', state);
    }
}, 2000);
