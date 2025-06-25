import { useState, useEffect, useCallback, useRef } from 'react';
import { router } from '@inertiajs/react';
import Toastify from 'toastify-js';
import { StockAlert, StockLevelChangedEvent } from '@/types/StockAlert';
import { EchoChannel } from '@/types/echo';

interface UseStockAlertsReturn {
    alerts: StockAlert[];
    unreadCount: number;
    isConnected: boolean;
    markAsRead: (alertId: string) => Promise<void>;
    markAllAsRead: () => Promise<void>;
    clearAlerts: () => Promise<void>;
    loadAlertsFromDatabase: () => Promise<void>;
}

export function useStockAlerts(): UseStockAlertsReturn {
    const [alerts, setAlerts] = useState<StockAlert[]>([]);
    const [isConnected, setIsConnected] = useState(false);
    const publicRef  = useRef<EchoChannel | null>(null);
    const privateRef = useRef<EchoChannel | null>(null);
    const warehouseRef = useRef<EchoChannel | null>(null);

    const loadAlertsFromDatabase = useCallback(async () => {
        try {
            const res = await fetch('/api/stock-alerts', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
            });
            if (res.ok) {
                const data: StockAlert[] = await res.json();
                setAlerts(data);
            } else {
                console.error('Failed loading DB alerts:', res.status);
            }
        } catch (e) {
            console.error('Error loading DB alerts:', e);
        }
    }, []);

    const mapEventToAlert = (data: StockLevelChangedEvent): StockAlert => ({
        id: `live-${Date.now()}`,
        type: data.alert_type || (data.new_quantity <= (data.min_stock ?? 0) ? 'low_stock' : 'overstock'),
        message:
            data.alert_type === 'low_stock'
                ? `Stok ${data.product_name} di ${data.warehouse_name} rendah (${data.new_quantity}/${data.min_stock})`
                : `Stok ${data.product_name} di ${data.warehouse_name} berlebih (${data.new_quantity}/${data.max_stock})`,
        inventory_id: data.inventory_id,
        product_name: data.product_name,
        warehouse_name: data.warehouse_name,
        current_quantity: data.new_quantity,
        min_stock: data.min_stock,
        max_stock: data.max_stock,
        product_id: data.product_id,
        warehouse_id: data.warehouse_id,
        timestamp: data.timestamp,
        created_at: new Date().toISOString(),
        read_at: null,
    });

    const showToastNotification = (alert: StockAlert) => {
        Toastify({
            text: `🔔 ${alert.message}`,
            duration: 5000,
            close: true,
            gravity: 'top',
            position: 'right',
            className: alert.type === 'low_stock' ? 'warning' : alert.type === 'overstock' ? 'error' : 'info',
            style: {
                background:
                    alert.type === 'low_stock'
                        ? 'linear-gradient(to right, #ff6b35, #ff8e53)'
                        : alert.type === 'overstock'
                        ? 'linear-gradient(to right, #ff4757, #ff6b7a)'
                        : 'linear-gradient(to right, #00b09b, #96c93d)',
                borderRadius: '8px',
                fontWeight: '500',
            },
            onClick: () => router.visit('/admin/inventory'),
        }).showToast();
    };

    useEffect(() => {
        if (!window.Echo) {
            console.warn('Echo belum siap');
            return;
        }

        const conn = window.Echo?.connector?.pusher?.connection;
        const onConn = () => setIsConnected(true);
        const onDisc = () => setIsConnected(false);
        if (conn) {
            conn.bind('connected', onConn);
            conn.bind('disconnected', onDisc);
            if (conn.state === 'connected') setIsConnected(true);
        }

        publicRef.current = window.Echo.channel('stock-alerts-public');
        publicRef.current.listen('stock.level.changed', (data: unknown) => {
            const newAlert = mapEventToAlert(data as StockLevelChangedEvent);
            setAlerts(prev => [newAlert, ...prev]);
            showToastNotification(newAlert);
        });

        privateRef.current = window.Echo.private('stock-alerts');
        privateRef.current.listen('stock.level.changed', (data: unknown) => {
            const newAlert = mapEventToAlert(data as StockLevelChangedEvent);
            setAlerts(prev => [newAlert, ...prev]);
            showToastNotification(newAlert);
        });

        const warehouseId = 1;
        warehouseRef.current = window.Echo.private(`warehouse.${warehouseId}`);
        warehouseRef.current.listen('stock.level.changed', (data: unknown) => {
            const newAlert = mapEventToAlert(data as StockLevelChangedEvent);
            setAlerts(prev => [newAlert, ...prev]);
            showToastNotification(newAlert);
        });

        loadAlertsFromDatabase();

        return () => {
            if (conn) {
                conn.unbind('connected', onConn);
                conn.unbind('disconnected', onDisc);
            }
            if (window.Echo && publicRef.current)  window.Echo.leave('stock-alerts-public');
            if (window.Echo && privateRef.current) window.Echo.leave('stock-alerts');
            if (window.Echo && warehouseRef.current) window.Echo.leave(`warehouse.${warehouseId}`);
        };
    }, [loadAlertsFromDatabase]);

    const getCsrfToken = () => {
        const tokenElement = document.querySelector('meta[name="csrf-token"]');
        return tokenElement ? tokenElement.getAttribute('content') : '';
    };

    const markAsRead = useCallback(async (id: string) => {
        setAlerts(prev => prev.map(a => (a.id === id ? { ...a, read_at: new Date().toISOString() } : a)));
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        await fetch(`/stock-alerts/${id}/read`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken } });
    }, []);

    const markAllAsRead = useCallback(async () => {
        const now = new Date().toISOString();
        setAlerts(prev => prev.map(a => ({ ...a, read_at: now })));
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        await fetch('/stock-alerts/read-all', { method: 'PATCH', headers: { 'X-CSRF-TOKEN': csrfToken } });
    }, []);

    const clearAlerts = useCallback(async () => {
        setAlerts([]);
        const csrfToken = getCsrfToken();
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        await fetch('/stock-alerts/clear', { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } });
    }, []);

    const unreadCount = alerts.filter(a => !a.read_at).length;

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
