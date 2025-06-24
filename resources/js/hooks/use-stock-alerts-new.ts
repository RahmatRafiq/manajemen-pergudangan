import { useState, useEffect, useCallback, useRef } from 'react';
import { router } from '@inertiajs/react';
import Toastify from 'toastify-js';
import { StockAlert, StockLevelChangedEvent } from '@/types/StockAlert';
import { EchoChannel } from '@/types/echo';

interface NotificationData {
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
    timestamp: string;
}

interface UseStockAlertsReturn {
    alerts: StockAlert[];
    unreadCount: number;
    isConnected: boolean;
    markAsRead: (alertId: string) => void;
    markAllAsRead: () => void;
    clearAlerts: () => void;
    loadAlertsFromDatabase: () => Promise<void>;
}

export function useStockAlerts(): UseStockAlertsReturn {
    const [alerts, setAlerts] = useState<StockAlert[]>([]);
    const [isConnected, setIsConnected] = useState(false);
    const channelRef = useRef<EchoChannel | null>(null);
    const privateChannelRef = useRef<EchoChannel | null>(null);

    // Load alerts from database
    const loadAlertsFromDatabase = useCallback(async () => {
        try {
            console.log('📚 Loading alerts from database...');
            const response = await fetch('/api/stock-alerts', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });

            if (response.ok) {
                const data = await response.json();
                console.log('📊 Database alerts loaded:', data.length);
                setAlerts(data);
            } else {
                console.error('❌ Failed to load alerts from database:', response.status);
            }
        } catch (error) {
            console.error('❌ Error loading alerts from database:', error);
        }
    }, []);

    // Setup WebSocket connections
    useEffect(() => {
        if (!window.Echo) {
            console.warn('⚠️ Echo not initialized yet');
            return;
        }

        console.log('🚀 Setting up stock alerts WebSocket listeners...');

        // Connection status monitoring
        const setupConnectionMonitoring = () => {
            if (window.Echo?.connector?.pusher?.connection) {
                const connection = window.Echo.connector.pusher.connection;
                
                const handleConnected = () => {
                    console.log('🟢 WebSocket connected - Stock alerts ready');
                    setIsConnected(true);
                };

                const handleDisconnected = () => {
                    console.log('🔴 WebSocket disconnected');
                    setIsConnected(false);
                };

                const handleError = (error?: Error) => {
                    console.error('❌ WebSocket error:', error);
                    setIsConnected(false);
                };

                connection.bind('connected', handleConnected);
                connection.bind('disconnected', handleDisconnected);
                connection.bind('error', handleError);

                // Check current state
                if (connection.state === 'connected') {
                    setIsConnected(true);
                }

                return () => {
                    connection.unbind('connected', handleConnected);
                    connection.unbind('disconnected', handleDisconnected);
                    connection.unbind('error', handleError);
                };
            }
        };

        const connectionCleanup = setupConnectionMonitoring();

        // Subscribe to public channel for stock level changes
        try {
            channelRef.current = window.Echo.channel('stock-alerts-public');
            console.log('📺 Subscribed to stock-alerts-public channel');

            channelRef.current.listen('stock.level.changed', (data: unknown) => {
                console.log('🎯 Received stock level change:', data);
                
                const eventData = data as StockLevelChangedEvent;
                
                const newAlert: StockAlert = {
                    id: `live-${Date.now()}-${Math.random()}`,
                    type: eventData.alert_type || 'low_stock',
                    message: generateAlertMessage(eventData),
                    inventory_id: eventData.inventory_id,
                    product_name: eventData.product_name,
                    warehouse_name: eventData.warehouse_name,
                    current_quantity: eventData.new_quantity,
                    min_stock: eventData.min_stock,
                    max_stock: eventData.max_stock,
                    product_id: eventData.product_id,
                    warehouse_id: eventData.warehouse_id,
                    timestamp: eventData.timestamp || new Date().toISOString(),
                    created_at: new Date().toISOString(),
                    read_at: null,
                };

                // Add to alerts list
                setAlerts(prev => [newAlert, ...prev]);

                // Show toast notification
                showToastNotification(newAlert);
            });

        } catch (error) {
            console.error('❌ Error setting up public channel:', error);
        }

        // Subscribe to private channel for notifications
        try {
            privateChannelRef.current = window.Echo.private('stock-alerts');
            console.log('🔐 Subscribed to private stock-alerts channel');

            privateChannelRef.current.listen('stock.alert', (data: unknown) => {
                console.log('🔔 Received stock alert notification:', data);
                
                const notificationData = data as NotificationData;
                
                const newAlert: StockAlert = {
                    id: `notification-${Date.now()}-${Math.random()}`,
                    type: notificationData.type || 'low_stock',
                    message: notificationData.message,
                    inventory_id: notificationData.inventory_id,
                    product_name: notificationData.product_name,
                    warehouse_name: notificationData.warehouse_name,
                    current_quantity: notificationData.current_quantity,
                    min_stock: notificationData.min_stock,
                    max_stock: notificationData.max_stock,
                    product_id: notificationData.product_id,
                    warehouse_id: notificationData.warehouse_id,
                    timestamp: notificationData.timestamp || new Date().toISOString(),
                    created_at: new Date().toISOString(),
                    read_at: null,
                };

                setAlerts(prev => [newAlert, ...prev]);
                showToastNotification(newAlert);
            });

        } catch (error) {
            console.error('❌ Error setting up private channel:', error);
        }

        // Load initial data
        loadAlertsFromDatabase();

        // Cleanup function
        return () => {
            console.log('🧹 Cleaning up stock alerts listeners...');
            
            if (connectionCleanup) {
                connectionCleanup();
            }

            if (channelRef.current) {
                window.Echo?.leave('stock-alerts-public');
                channelRef.current = null;
            }

            if (privateChannelRef.current) {
                window.Echo?.leave('stock-alerts');
                privateChannelRef.current = null;
            }
        };
    }, [loadAlertsFromDatabase]);

    const generateAlertMessage = (data: StockLevelChangedEvent): string => {
        if (data.alert_type === 'low_stock') {
            return `Stok produk ${data.product_name} di ${data.warehouse_name} rendah (${data.new_quantity}/${data.min_stock})`;
        } else if (data.alert_type === 'overstock') {
            return `Stok produk ${data.product_name} di ${data.warehouse_name} berlebih (${data.new_quantity}/${data.max_stock})`;
        }
        return `Stok produk ${data.product_name} di ${data.warehouse_name} berubah: ${data.old_quantity} → ${data.new_quantity}`;
    };

    const showToastNotification = (alert: StockAlert) => {
        const toastConfig = {
            text: `🔔 ${alert.message}`,
            duration: 5000,
            close: true,
            gravity: 'top' as const,
            position: 'right' as const,
            className: alert.type === 'low_stock' ? 'warning' : alert.type === 'overstock' ? 'error' : 'info',
            style: {
                background: alert.type === 'low_stock' 
                    ? 'linear-gradient(to right, #ff6b35, #ff8e53)' 
                    : alert.type === 'overstock'
                    ? 'linear-gradient(to right, #ff4757, #ff6b7a)'
                    : 'linear-gradient(to right, #00b09b, #96c93d)',
                borderRadius: '8px',
                fontWeight: '500',
            },
            onClick: () => {
                router.visit('/admin/inventory');
            }
        };

        Toastify(toastConfig).showToast();
        console.log('🍞 Toast notification shown for alert:', alert.type);
    };

    const markAsRead = useCallback((alertId: string) => {
        setAlerts(prev => 
            prev.map(alert => 
                alert.id === alertId 
                    ? { ...alert, read_at: new Date().toISOString() }
                    : alert
            )
        );
    }, []);

    const markAllAsRead = useCallback(() => {
        const now = new Date().toISOString();
        setAlerts(prev => 
            prev.map(alert => ({ ...alert, read_at: now }))
        );
    }, []);

    const clearAlerts = useCallback(() => {
        setAlerts([]);
    }, []);

    const unreadCount = alerts.filter(alert => !alert.read_at).length;

    return {
        alerts,
        unreadCount,
        isConnected,
        markAsRead,
        markAllAsRead,
        clearAlerts,
        loadAlertsFromDatabase,
    };
}
