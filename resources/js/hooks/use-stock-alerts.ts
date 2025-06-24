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
        console.log('🚨 addAlert called with:', alert);
        
        if (!mountedRef.current) {
            console.log('❌ Component not mounted, skipping alert');
            return;
        }

        // Show toast notification IMMEDIATELY when alert is received
        const alertTitle = alert.type === 'low_stock' ? '🔻 Stok Rendah' : '🔺 Stok Berlebih';
        const alertMessage = `${alert.product_name} di ${alert.warehouse_name} - Stok: ${alert.current_quantity}`;
        
        console.log('🍞 Attempting to show Toastify:', { alertTitle, alertMessage });
        
        try {
            Toastify({
                text: `${alertTitle}: ${alertMessage}`,
                className: alert.type === 'low_stock' ? 'error' : 'warning',
                duration: 5000,
                close: true,
                gravity: "top",
                position: "right",
                onClick: () => window.open(`/inventory?search=${alert.product_name}`, '_blank'),
                style: {
                    background: alert.type === 'low_stock' 
                        ? "linear-gradient(to right, #ff5f6d, #ffc371)" 
                        : "linear-gradient(to right, #ffecd2, #fcb69f)",
                },
            }).showToast();
            
            console.log('✅ Toastify notification shown successfully');
        } catch (toastError) {
            console.error('❌ Error showing Toastify:', toastError);
        }

        setState(prevState => {
            // Avoid duplicate alerts
            const exists = prevState.alerts.some(
                existing => existing.inventory_id === alert.inventory_id && 
                           existing.type === alert.type
            );

            if (exists) {
                console.log('⚠️ Duplicate alert detected, skipping state update');
                return prevState;
            }

            const newAlerts = [alert, ...prevState.alerts];
            
            // Keep only last 50 alerts
            if (newAlerts.length > 50) {
                newAlerts.splice(50);
            }

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
                    method: 'POST',
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
            unreadCount: Math.max(0, prevState.unreadCount - 1),
        }));
    }, []);

    const markAllAsRead = useCallback(async () => {
        try {
            await fetch('/stock-alerts/read-all', {
                method: 'POST',
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
    }, []);

    const loadAlertsFromDatabase = useCallback(async () => {
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
        } catch (error) {
            console.error('Failed to load alerts from database:', error);
        }
    }, []);

    // Effect to setup Echo listeners
    useEffect(() => {
        mountedRef.current = true;

        // Request notification permission on mount
        requestNotificationPermission();

        // Load existing alerts from database
        loadAlertsFromDatabase();        // Start listening for real-time events
        const setupEcho = () => {
            if (!window.Echo) {
                console.log('⚠️ Echo not available, retrying in 1 second...');
                setTimeout(setupEcho, 1000);
                return;
            }

            if (isListeningRef.current) {
                console.log('ℹ️ Already listening, skipping setup');
                return;
            }            try {
                console.log('🔗 Setting up Echo listeners...');
                console.log('🔍 Testing public channel for live updates...');
                  // Listen to PUBLIC channel first for testing
                const publicChannel = window.Echo.channel('stock-alerts-public') as EchoChannel;
                channelRef.current = publicChannel;
                
                publicChannel.listen('stock.level.changed', (data: StockLevelChangedEvent) => {
                        console.log('📊 *** LIVE UPDATE EVENT RECEIVED (PUBLIC CHANNEL) ***');
                        console.log('📊 stock.level.changed event received:', data);
                        console.log('🎯 Alert type detected:', data.alert_type);
                        
                        // If there's an alert, add it
                        if (data.alert_type) {
                            console.log('✅ Creating alert from stock level change');
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
                            console.log('📤 Calling addAlert with:', alert);
                            addAlert(alert);
                        } else {
                            console.log('ℹ️ No alert needed for this stock level change');
                        }
                    });
                
                console.log('🎯 Subscribed to PUBLIC channel: stock-alerts-public');

                // Check connection status
                try {
                    if (window.Echo.connector?.pusher?.connection) {
                        window.Echo.connector.pusher.connection.bind('connected', () => {
                            if (mountedRef.current) {
                                setState(prevState => ({ ...prevState, isConnected: true }));
                                console.log('🟢 Connected to stock alerts channel');
                            }
                        });

                        window.Echo.connector.pusher.connection.bind('disconnected', () => {
                            if (mountedRef.current) {
                                setState(prevState => ({ ...prevState, isConnected: false }));
                                console.log('🔴 Disconnected from stock alerts channel');
                            }
                        });
                    }
                } catch (connectionError) {
                    console.warn('⚠️ Could not bind to connection events:', connectionError);
                }

                isListeningRef.current = true;
                setState(prevState => ({ ...prevState, isListening: true }));
                console.log('✅ Echo listeners setup complete');

            } catch (error) {
                console.error('❌ Failed to setup Echo listeners:', error);
            }
        };

        setupEcho();

        return () => {
            mountedRef.current = false;
            if (channelRef.current) {
                window.Echo.leave('stock-alerts');
                channelRef.current = null;
            }
            isListeningRef.current = false;        };
    }, [addAlert, requestNotificationPermission, loadAlertsFromDatabase]);

    // Auto-refresh fallback jika real-time gagal
    useEffect(() => {
        console.log('🔄 Setting up auto-refresh fallback...');
        
        const autoRefreshInterval = setInterval(async () => {
            console.log('🔄 Auto-refresh: Checking for new alerts...');
            
            try {
                const response = await fetch('/stock-alerts', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                });
                
                if (response.ok) {
                    const data = await response.json();
                    const newAlerts = data.alerts || [];
                    
                    setState(prevState => {
                        // Cek apakah ada alert baru
                        if (newAlerts.length > prevState.alerts.length) {
                            console.log('🎉 Auto-refresh: Found new alerts!', {
                                old: prevState.alerts.length,
                                new: newAlerts.length
                            });
                            
                            // Tampilkan Toastify untuk alert terbaru
                            const latestAlerts = newAlerts.slice(0, newAlerts.length - prevState.alerts.length);
                            latestAlerts.forEach((alert: StockAlert) => {
                                const alertTitle = alert.type === 'low_stock' ? '🔻 Stok Rendah' : '🔺 Stok Berlebih';
                                const alertMessage = `${alert.product_name} di ${alert.warehouse_name} - Stok: ${alert.current_quantity}`;
                                
                                Toastify({
                                    text: `${alertTitle}: ${alertMessage}`,
                                    className: alert.type === 'low_stock' ? 'error' : 'warning',
                                    duration: 5000,
                                    close: true,
                                    gravity: "top",
                                    position: "right",
                                    style: {
                                        background: alert.type === 'low_stock' 
                                            ? "linear-gradient(to right, #ff5f6d, #ffc371)" 
                                            : "linear-gradient(to right, #ffecd2, #fcb69f)",
                                    },
                                }).showToast();
                            });
                        }
                        
                        return {
                            ...prevState,
                            alerts: newAlerts,
                            unreadCount: data.unread_count || 0,
                            lastUpdated: new Date(),
                        };
                    });
                }
            } catch (error) {
                console.error('❌ Auto-refresh failed:', error);
            }
        }, 5000); // Refresh setiap 5 detik
        
        return () => {
            console.log('🛑 Clearing auto-refresh interval');
            clearInterval(autoRefreshInterval);
        };
    }, []); // Only run once

    const startListening = useCallback(() => {
        // This is handled by the useEffect now
        console.log('startListening called - handled by useEffect');
    }, []);

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
    }, []);

    return {
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
