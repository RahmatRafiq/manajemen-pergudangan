import { useState, useEffect, useCallback, useMemo } from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { useStockAlerts } from '@/hooks/use-stock-alerts';
import { StockAlertCard } from '@/components/stock-alert-card';
import { StockAlertStats } from '@/components/stock-alert-stats';
import { type BreadcrumbItem } from '@/types';
import { type StockAlert } from '@/types/StockAlert';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Bell, Package, Loader2, Search, Filter, RefreshCw, CheckCircle } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Stock Alerts',
        href: '/stock-alerts',
    },
];

export default function StockAlertsPage() {
    const { alerts, unreadCount, isConnected, markAsRead, markAllAsRead, clearAlerts, loadAlertsFromDatabase } = useStockAlerts();
    const [filteredAlerts, setFilteredAlerts] = useState<StockAlert[]>([]);
    const [searchTerm, setSearchTerm] = useState('');
    const [filterType, setFilterType] = useState<'all' | 'low_stock' | 'overstock'>('all');
    const [isLoading, setIsLoading] = useState(false);

    // Use alerts from hook (which includes database alerts)
    const allAlerts = useMemo(() => alerts, [alerts]);

    const filterAlerts = useCallback(() => {
        let filtered = allAlerts;

        // Filter by type
        if (filterType !== 'all') {
            filtered = filtered.filter(alert => alert.type === filterType);
        }

        // Filter by search term
        if (searchTerm) {
            filtered = filtered.filter(alert =>
                alert.product_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                alert.warehouse_name.toLowerCase().includes(searchTerm.toLowerCase()) ||
                alert.message.toLowerCase().includes(searchTerm.toLowerCase())
            );
        }

        // Sort by timestamp (newest first)
        filtered.sort((a, b) => {
            const timeA = new Date(a.timestamp || a.created_at || 0).getTime();
            const timeB = new Date(b.timestamp || b.created_at || 0).getTime();
            return timeB - timeA;
        });

        setFilteredAlerts(filtered);
    }, [allAlerts, filterType, searchTerm]);

    useEffect(() => {
        filterAlerts();
    }, [filterAlerts]);    const handleRefresh = async () => {
        setIsLoading(true);
        try {
            await loadAlertsFromDatabase();
        } finally {
            setIsLoading(false);
        }
    };

    const handleMarkAllAsRead = () => {
        markAllAsRead();
    };

    const handleClearAll = () => {
        clearAlerts();
    };

    const lowStockCount = filteredAlerts.filter(alert => alert.type === 'low_stock').length;
    const overstockCount = filteredAlerts.filter(alert => alert.type === 'overstock').length;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Stock Alerts" />
            
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                {/* Header */}
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/20">
                            <Bell className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                        </div>
                        <div>
                            <h1 className="text-2xl font-semibold tracking-tight">Stock Alerts</h1>
                            <p className="text-sm text-muted-foreground">
                                Monitor inventory levels and receive notifications
                            </p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2">
                        <div className="flex items-center gap-2">
                            <div className={`h-2 w-2 rounded-full ${isConnected ? 'bg-green-500' : 'bg-red-500'}`} />
                            <span className="text-sm text-muted-foreground">
                                {isConnected ? 'Connected' : 'Disconnected'}
                            </span>
                        </div>
                        
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleRefresh}
                            disabled={isLoading}
                        >
                            {isLoading ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                <RefreshCw className="h-4 w-4" />
                            )}
                            Refresh
                        </Button>

                        {unreadCount > 0 && (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={handleMarkAllAsRead}
                            >
                                <CheckCircle className="h-4 w-4" />
                                Mark All Read
                            </Button>
                        )}

                        {filteredAlerts.length > 0 && (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={handleClearAll}
                            >
                                Clear All
                            </Button>
                        )}
                    </div>
                </div>

                {/* Stats Cards */}
                <StockAlertStats
                    totalAlerts={filteredAlerts.length}
                    lowStockCount={lowStockCount}
                    overstockCount={overstockCount}
                    unreadCount={unreadCount}
                />

                {/* Filters */}
                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <CardTitle className="text-lg">Alert History</CardTitle>
                                <CardDescription>
                                    {filteredAlerts.length} of {allAlerts.length} alerts
                                </CardDescription>
                            </div>
                            
                            <div className="flex flex-col gap-2 sm:flex-row sm:items-center">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Search alerts..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-9 sm:w-64"
                                    />
                                </div>
                                
                                <Select value={filterType} onValueChange={(value: 'all' | 'low_stock' | 'overstock') => setFilterType(value)}>
                                    <SelectTrigger className="w-full sm:w-40">
                                        <Filter className="h-4 w-4" />
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Alerts</SelectItem>
                                        <SelectItem value="low_stock">Low Stock</SelectItem>
                                        <SelectItem value="overstock">Overstock</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                    </CardHeader>
                    
                    <CardContent>
                        {filteredAlerts.length === 0 ? (
                            <div className="flex h-64 flex-col items-center justify-center text-center">
                                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                                    <Package className="h-8 w-8 text-gray-400" />
                                </div>
                                <h3 className="mt-4 text-lg font-medium">No alerts found</h3>
                                <p className="mt-2 text-sm text-muted-foreground">
                                    {searchTerm || filterType !== 'all'
                                        ? 'Try adjusting your search or filter criteria.'
                                        : 'All inventory levels are within normal ranges.'}
                                </p>
                            </div>
                        ) : (
                            <div className="space-y-4">
                                {filteredAlerts.map((alert, index) => (                                    <StockAlertCard
                                    key={alert.id || `${alert.inventory_id}-${alert.type}-${index}`}
                                        alert={alert}
                                        onMarkAsRead={() => alert.id && markAsRead(alert.id)}
                                    />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
