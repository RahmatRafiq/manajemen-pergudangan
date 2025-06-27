import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import StatsCard from '@/components/dashboard/StatsCard';
import RecentTransactions from '@/components/dashboard/RecentTransactions';
import LowStockAlerts from '@/components/dashboard/LowStockAlerts';
import TransactionChart from '@/components/dashboard/TransactionChart';
import TopMovingProducts from '@/components/dashboard/TopMovingProducts';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { 
    Package, 
    Warehouse, 
    AlertTriangle, 
    TrendingUp, 
    Activity,
    Filter,
    Eye,
    ArrowRight
} from 'lucide-react';

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

interface MovementAnalysisItem {
    product_name: string;
    warehouse_name: string;
    total_quantity: number;
    total_movement: number;
    movement_ratio: number;
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
    movement_analysis: MovementAnalysisItem[];
    warehouse_performance: WarehousePerformanceItem[];
    is_global_access: boolean;
    available_warehouses: { id: number; name: string }[];
    selected_warehouse_id: number | null;
    user_role: string;
}

export default function Dashboard({
    stats,
    transaction_stats,
    daily_transactions,
    recent_transactions,
    top_moving_products,
    low_stock_details,
    movement_analysis,
    warehouse_performance,
    is_global_access,
    available_warehouses,
    selected_warehouse_id,
}: DashboardProps) {
    const handleWarehouseFilter = (warehouseId: string) => {
        const params = warehouseId === 'all' ? {} : { warehouse_id: warehouseId };
        router.get('/dashboard', params, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const getGreeting = () => {
        const hour = new Date().getHours();
        if (hour < 12) return 'Good Morning';
        if (hour < 17) return 'Good Afternoon';
        return 'Good Evening';
    };

    const selectedWarehouse = selected_warehouse_id 
        ? available_warehouses.find(w => w.id === selected_warehouse_id)
        : null;

    return (
        <AppLayout>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            {getGreeting()}! 👋
                        </h1>
                        <p className="text-muted-foreground"></p>
                        <p className="text-muted-foreground">
                            Welcome back to your warehouse management dashboard
                            {selectedWarehouse && (
                                <span className="font-medium"> - {selectedWarehouse.name}</span>
                            )}
                        </p>
                    </div>
                    
                    <div className="flex items-center gap-3">
                        {available_warehouses.length > 1 && (
                            <div className="flex items-center gap-2">
                                <Filter className="h-4 w-4 text-muted-foreground" />
                                <Select
                                    value={selected_warehouse_id?.toString() || 'all'}
                                    onValueChange={handleWarehouseFilter}
                                >
                                    <SelectTrigger className="w-48">
                                        <SelectValue placeholder="Select warehouse" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Warehouses</SelectItem>
                                        {available_warehouses.map((warehouse) => (
                                            <SelectItem key={warehouse.id} value={warehouse.id.toString()}>
                                                {warehouse.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        )}
                        <Link href="/stock-transaction/create">
                            <Button>
                                <Activity className="h-4 w-4 mr-2" />
                                New Transaction
                            </Button>
                        </Link>
                    </div>
                </div>
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <StatsCard
                        title="Total Products"
                        value={stats.total_products.toLocaleString()}
                        icon={<Package className="h-4 w-4 text-blue-600 dark:text-blue-400" />}
                    />
                    <StatsCard
                        title="Warehouses"
                        value={stats.total_warehouses.toLocaleString()}
                        icon={<Warehouse className="h-4 w-4 text-green-600 dark:text-green-400" />}
                    />
                    <StatsCard
                        title="Total Inventory"
                        value={`${stats.total_inventory_value.toLocaleString()} units`}
                        icon={<Package className="h-4 w-4 text-purple-600 dark:text-purple-400" />}
                    />
                    <StatsCard
                        title="Stock Alerts"
                        value={stats.stock_alerts.toLocaleString()}
                        icon={<AlertTriangle className="h-4 w-4 text-orange-600 dark:text-orange-400" />}
                        alert={stats.stock_alerts > 0}
                    />
                </div>
                {stats.stock_alerts > 0 && (
                    <Card className="border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950/50">
                        <CardContent className="flex items-center justify-between p-4">
                            <div className="flex items-center gap-3">
                                <AlertTriangle className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                                <div>
                                    <div className="font-medium text-orange-900 dark:text-orange-100">Stock Attention Required</div>
                                    <div className="text-sm text-orange-700 dark:text-orange-300">
                                        {stats.low_stock_items} items are running low, {stats.overstock_items} items are overstocked
                                    </div>
                                </div>
                            </div>
                            <Link href="/stock-alerts">
                                <Button variant="outline" size="sm">
                                    <Eye className="h-4 w-4 mr-2" />
                                    View Alerts
                                </Button>
                            </Link>
                        </CardContent>
                    </Card>
                )}
                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="lg:col-span-2 space-y-6">
                        <TransactionChart 
                            dailyData={daily_transactions}
                            stats={transaction_stats}
                        />
                        <TopMovingProducts products={top_moving_products} />
                        {movement_analysis.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <div className="flex items-center justify-between">
                                        <CardTitle className="flex items-center gap-2">
                                            <TrendingUp className="h-5 w-5" />
                                            Movement Analysis
                                        </CardTitle>
                                        <Link href="/inventory/sorted/global">
                                            <Button variant="outline" size="sm">
                                                View Full Analysis
                                                <ArrowRight className="h-4 w-4 ml-2" />
                                            </Button>
                                        </Link>
                                    </div>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {movement_analysis.slice(0, 5).map((item, index) => (
                                            <div key={index} className="flex items-center justify-between p-3 rounded-lg border border-border">
                                                <div>
                                                    <div className="font-medium text-sm">
                                                        {item.product_name}
                                                    </div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {item.warehouse_name} • Ratio: {item.movement_ratio}
                                                    </div>
                                                </div>
                                                <div className="text-right">                                                <div className={`text-xs px-2 py-1 rounded ${
                                                    item.recommendation.status === 'danger' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200' :
                                                    item.recommendation.status === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200' :
                                                    item.recommendation.status === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-200' :
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-200'
                                                }`}>
                                                        {item.movement_category.replace('_', ' ').toUpperCase()}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                        {is_global_access && warehouse_performance.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Warehouse className="h-5 w-5" />
                                        Warehouse Performance
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="grid gap-4 md:grid-cols-2">
                                        {warehouse_performance.map((warehouse) => (
                                            <div key={warehouse.id} className="p-4 rounded-lg border border-border">
                                                <div className="flex items-center justify-between mb-2">
                                                    <div className="font-medium">{warehouse.name}</div>
                                                    <div className="text-xs text-muted-foreground">
                                                        {warehouse.reference}
                                                    </div>
                                                </div>
                                                <div className="grid grid-cols-2 gap-2 text-sm">
                                                    <div>
                                                        <div className="text-muted-foreground">Users</div>
                                                        <div className="font-medium">{warehouse.users_count}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-muted-foreground">Products</div>
                                                        <div className="font-medium">{warehouse.inventory_count}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-muted-foreground">Total Stock</div>
                                                        <div className="font-medium">{warehouse.total_stock.toLocaleString()}</div>
                                                    </div>
                                                    <div>
                                                        <div className="text-muted-foreground">Activity</div>
                                                        <div className="font-medium">{warehouse.recent_activity}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                    <div className="space-y-6">
                        <LowStockAlerts items={low_stock_details} />
                        <RecentTransactions transactions={recent_transactions} />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
