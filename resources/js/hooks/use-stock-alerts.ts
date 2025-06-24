import { EchoChannel } from '@/types/global';
import { useState, useEffect, useCallback, useRef } from 'react';
import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

interface StockAlert {
    type: 'low_stock' | 'overstock';
    message: string;
    inventory_id: number;
    product_name: string;
    warehouse_name: string;
    current_quantity: number;
    min_stock: number | null;
    max_stock: number | null;
    product_id: number;
    warehouse_id: number;
    timestamp?: string;
    id?: string;
    created_at?: string;
}

interface StockLevelChangedEvent {
    inventory_id: number;
    product_id: number;
    warehouse_id: number;
    product_name: string;
    warehouse_name: string;
    old_quantity: number;
    new_quantity: number;
    min_stock: number | null;
    max_stock: number | null;
    alert_type: 'low_stock' | 'overstock' | null;
    timestamp: string;
}

interface UseStockAlertsState {
    alerts: StockAlert[];
    unreadCount: number;
    isConnected: boolean;
    isListening: boolean;
    lastUpdated: Date | null;
}

export function useStockAlerts() {
    const [state, setState] = useState<UseStockAlertsState>({
        alerts: [],
        unreadCount: 0,
        isConnected: false,
        isListening: false,
        lastUpdated: null,
    });

    const channelRef = useRef<EchoChannel | null>(null);
    const mountedRef = useRef(true);
    const isListeningRef = useRef(false);

    const addAlert = useCallback((alert: StockAlert) => {
        if (!mountedRef.current) return;

        setState(prevState => {
            // Avoid duplicate alerts
            const exists = prevState.alerts.some(
                existing => existing.inventory_id === alert.inventory_id && 
                           existing.type === alert.type
            );

            if (exists) return prevState;

            const newAlerts = [alert, ...prevState.alerts];
            
            // Keep only last 50 alerts
            if (newAlerts.length > 50) {
                newAlerts.splice(50);
            }            // Show toast notification
            const alertTitle = alert.type === 'low_stock' ? '🔻 Stok Rendah' : '🔺 Stok Berlebih';
            const alertMessage = `${alert.product_name} di ${alert.warehouse_name} - Stok: ${alert.current_quantity}`;
            
            console.log('🍞 Showing Toastify notification:', { alertTitle, alertMessage });
            
            Toastify({
                text: `${alertTitle}: ${alertMessage}`,
                className: alert.type === 'low_stock' ? 'error' : 'warning',
                duration: 5000,
                onClick: () => window.open(`/inventory?search=${alert.product_name}`, '_blank'),
                style: {
                    background: alert.type === 'low_stock' 
                        ? "linear-gradient(to right, #ff5f6d, #ffc371)" 
                        : "linear-gradient(to right, #ffecd2, #fcb69f)",
                },
            }).showToast();

            // Show browser notification if supported and permitted
            if ('Notification' in window && Notification.permission === 'granted') {
                const title = alert.type === 'low_stock' ? 
                    '🔻 Stok Rendah' : 
                    '🔺 Stok Berlebih';

                const notification = new Notification(title, {
                    body: `${alert.product_name} di ${alert.warehouse_name}\nStok: ${alert.current_quantity}`,
                    icon: '/logo.svg',
                    tag: `stock-alert-${alert.inventory_id}`,
                });

                // Auto close after 5 seconds
                setTimeout(() => notification.close(), 5000);
            }

            return {
                ...prevState,
                alerts: newAlerts,
                unreadCount: prevState.unreadCount + 1,
                lastUpdated: new Date(),
            };
        });
    }, []); // No dependencies - function is stable

    const markAsRead = useCallback(async (alertId?: string) => {
        if (alertId) {
            try {
                await fetch(`/stock-alerts/${alertId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });
            } catch (error) {
                console.error('Failed to mark alert as read:', error);
            }
        }
        
        setState(prevState => ({
            ...prevState,
            unreadCount: alertId 
                ? Math.max(0, prevState.unreadCount - 1)
                : 0,
        }));
    }, []);

    const markAllAsRead = useCallback(async () => {
        try {
            await fetch('/stock-alerts/read-all', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
        } catch (error) {
            console.error('Failed to mark all alerts as read:', error);
        }
        
        setState(prevState => ({
            ...prevState,
            unreadCount: 0,
        }));
    }, []);

    const clearAlerts = useCallback(async () => {
        try {
            await fetch('/stock-alerts/clear', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
        } catch (error) {
            console.error('Failed to clear alerts:', error);
        }
        
        setState(prevState => ({
            ...prevState,
            alerts: [],
            unreadCount: 0,
        }));
    }, []);

    const requestNotificationPermission = useCallback(async () => {
        if ('Notification' in window && Notification.permission === 'default') {
            await Notification.requestPermission();
        }
    }, []);    const loadAlertsFromDatabase = useCallback(async () => {
        try {
            const response = await fetch('/stock-alerts', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            
            if (!response.ok) {
                throw new Error(`Failed to load alerts: ${response.status} ${response.statusText}`);
            }
            
            const data = await response.json();
            
            setState(prevState => ({
                ...prevState,
                alerts: data.alerts || [],
                unreadCount: data.unread_count || 0,
                lastUpdated: new Date(),
            }));
            
            console.log('Loaded alerts from database:', data);
        } catch (error) {
            console.error('Failed to load alerts from database:', error);
        }
    }, []);

    // Load initial alerts on mount
    useEffect(() => {
        loadAlertsFromDatabase();
    }, [loadAlertsFromDatabase]);

    // Auto-start listening when hook is used - moved inside useEffect to avoid infinite loops
    useEffect(() => {
        console.log('🔄 useStockAlerts hook mounted');
        console.log('📊 Echo available:', !!window.Echo);
        console.log('🌍 Window Echo object:', window.Echo);
        
        // Request notification permission
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
        
        // Start listening for alerts - only if not already listening
        if (!isListeningRef.current && window.Echo) {
            try {
                // Listen to stock alerts channel
                channelRef.current = window.Echo.private('stock-alerts')
                    .listen('stock.alert', (data: StockAlert) => {
                        console.log('Stock alert received:', data);
                        addAlert(data);
                    })
                    .listen('stock.level.changed', (data: StockLevelChangedEvent) => {
                        console.log('Stock level changed:', data);
                        
                        // If there's an alert, add it
                        if (data.alert_type) {
                            const alert: StockAlert = {
                                type: data.alert_type,
                                message: `Stok ${data.product_name} di ${data.warehouse_name} ${
                                    data.alert_type === 'low_stock' ? 'rendah' : 'berlebih'
                                }`,
                                inventory_id: data.inventory_id,
                                product_name: data.product_name,
                                warehouse_name: data.warehouse_name,
                                current_quantity: data.new_quantity,
                                min_stock: data.min_stock,
                                max_stock: data.max_stock,
                                product_id: data.product_id,
                                warehouse_id: data.warehouse_id,
                                timestamp: data.timestamp,
                            };
                            addAlert(alert);
                        }
                    });

                // Check connection status (optional)
                try {
                    if (window.Echo.connector?.pusher?.connection) {
                        window.Echo.connector.pusher.connection.bind('connected', () => {
                            if (mountedRef.current) {
                                setState(prevState => ({ ...prevState, isConnected: true }));
                                console.log('Connected to stock alerts');
                            }
                        });

                        window.Echo.connector.pusher.connection.bind('disconnected', () => {
                            if (mountedRef.current) {
                                setState(prevState => ({ ...prevState, isConnected: false }));
                                console.log('Disconnected from stock alerts');
                            }
                        });
                    }
                } catch (connectionError) {
                    console.warn('Could not bind to connection events:', connectionError);
                }

                isListeningRef.current = true;
                setState(prevState => ({ ...prevState, isListening: true }));
                console.log('Started listening for stock alerts');

            } catch (error) {
                console.error('Failed to start listening for stock alerts:', error);
            }
        }

        return () => {
            mountedRef.current = false;
            if (channelRef.current) {
                window.Echo.leave('stock-alerts');
                channelRef.current = null;
            }
            isListeningRef.current = false;        };
    }, [addAlert]); // addAlert is stable with empty deps

    const startListening = useCallback(() => {
        if (isListeningRef.current || !window.Echo) {
            return;
        }

        try {
            // Listen to stock alerts channel
            channelRef.current = window.Echo.private('stock-alerts')
                .listen('stock.alert', (data: StockAlert) => {
                    console.log('Stock alert received:', data);
                    addAlert(data);
                })
                .listen('stock.level.changed', (data: StockLevelChangedEvent) => {
                    console.log('Stock level changed:', data);
                    
                    // If there's an alert, add it
                    if (data.alert_type) {
                        const alert: StockAlert = {
                            type: data.alert_type,
                            message: `Stok ${data.product_name} di ${data.warehouse_name} ${
                                data.alert_type === 'low_stock' ? 'rendah' : 'berlebih'
                            }`,
                            inventory_id: data.inventory_id,
                            product_name: data.product_name,
                            warehouse_name: data.warehouse_name,
                            current_quantity: data.new_quantity,
                            min_stock: data.min_stock,
                            max_stock: data.max_stock,
                            product_id: data.product_id,
                            warehouse_id: data.warehouse_id,
                            timestamp: data.timestamp,
                        };
                        addAlert(alert);
                    }
                });

            isListeningRef.current = true;
            setState(prevState => ({ ...prevState, isListening: true }));
            console.log('Started listening for stock alerts');

        } catch (error) {
            console.error('Failed to start listening for stock alerts:', error);
        }
    }, [addAlert]);

    const stopListening = useCallback(() => {
        if (channelRef.current) {
            window.Echo.leave('stock-alerts');
            channelRef.current = null;
        }
        isListeningRef.current = false;
        setState(prevState => ({
            ...prevState,
            isListening: false,
            isConnected: false,
        }));
    }, []);    return {
        ...state,
        addAlert,
        markAsRead,
        markAllAsRead,
        clearAlerts,
        startListening,
        stopListening,
        requestNotificationPermission,
        loadAlertsFromDatabase,
    };
}
