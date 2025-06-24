export interface EchoChannel {
    listen: (event: string, callback: (data: unknown) => void) => EchoChannel;
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
    leave: (channel: string) => void;
    connector?: EchoPusherConnector;
}

declare global {
    interface Window {
        Echo?: EchoInstance;
    }
}

export {};
