export interface StockAlert {
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
    read_at?: string | null;
}

export interface StockLevelChangedEvent {
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

export interface StockNotificationState {
    alerts: StockAlert[];
    unreadCount: number;
    isConnected: boolean;
    lastUpdated: Date | null;
}
