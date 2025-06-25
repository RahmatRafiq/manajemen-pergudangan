import type { route as routeFn } from 'ziggy-js';
import type { ActivityLog } from '@/types';

interface EchoChannel {
    listen: <T = unknown>(event: string, callback: (data: T) => void) => EchoChannel;
}

interface EchoPusherConnection {
    bind: (event: string, callback: () => void) => void;
}

interface EchoPusherConnector {
    pusher?: {
        connection?: EchoPusherConnection;
    };
}

interface EchoInstance {
    private: (channel: string) => EchoChannel;
    channel: (channelName: string) => { 
        listen: (eventName: string, callback: (data: ActivityLog) => void) => void; 
    };
    leave: (channelName: string) => void;
    connector?: EchoPusherConnector;
}

declare global {
    const route: typeof routeFn;
    
    interface Window {
        Echo: EchoInstance;
    }
}
