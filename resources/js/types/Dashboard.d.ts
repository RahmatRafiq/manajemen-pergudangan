interface Transaction {
    id: number;
    type: string;
    quantity: number;
    reference: string;
    created_at: string;
    inventory: {
        product: { name: string; sku: string };
        warehouse: { name: string };
    };
    creator: { name: string };
}

interface TopMovingProduct {
    product_name: string;
    product_sku: string;
    warehouse_name: string;
    total_movement: number;
    transaction_count: number;
    current_stock: number;
}

interface LowStockItem {
    product_name: string;
    product_sku: string;
    warehouse_name: string;
    current_stock: number;
    min_stock: number;
    percentage: number;
}

interface OverstockItem {
    product_name: string;
    product_sku: string;
    warehouse_name: string;
    current_stock: number;
    max_stock: number;
    percentage: number;
}

interface MovementAnalysisItem {
    product_name: string;
    warehouse_count: number;
    total_quantity: number;
    total_movement: number;
    movement_category: string;
    recommendation: {
        status: string;
        text: string;
        action: string;
    };
}

interface WarehousePerformanceItem {
    id: number;
    name: string;
    reference: string;
    users_count: number;
    inventory_count: number;
    total_stock: number;
    recent_activity: number;
}

interface DailyTransactionItem {
    date: string;
    type: string;
    count: number;
    quantity: string;
}

interface DashboardProps {
    stats: {
        total_products: number;
        total_warehouses: number;
        total_inventory_value: number;
        total_users: number;
        stock_alerts: number;
        low_stock_items: number;
        overstock_items: number;
    };
    transaction_stats: Record<string, { count: number; total_quantity: number }>;
    daily_transactions: Record<string, DailyTransactionItem[]>;
    recent_transactions: Transaction[];
    top_moving_products: TopMovingProduct[];
    low_stock_details: LowStockItem[];
    overstock_details: OverstockItem[];
    movement_analysis: MovementAnalysisItem[];
    warehouse_performance: WarehousePerformanceItem[];
    is_global_access: boolean;
    available_warehouses: { id: number; name: string }[];
    selected_warehouse_id: number | null;
    user_role: string;
}