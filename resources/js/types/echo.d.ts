export interface EchoChannel {
    listen: (event: string, callback: (data: unknown) => void) => EchoChannel;
}

interface EchoPusherConnection {
    bind: (event: string, callback: (error?: Error) => void) => void;
    unbind: (event: string, callback: (error?: Error) => void) => void;
    state: string;
}

interface EchoPusherConnector {
    pusher?: {
        connection?: EchoPusherConnection;
    };
}

interface EchoInstance {
    private: (channel: string) => EchoChannel;
    channel: (channel: string) => EchoChannel;
    leave: (channel: string) => void;
    connector?: EchoPusherConnector;
}

declare global {
    interface Window {
        Echo?: EchoInstance;
    }
}

export {};
