import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import { AlertTriangle, Package, Warehouse } from 'lucide-react';

interface LowStockItem {
    product_name: string;
    product_sku: string;
    warehouse_name: string;
    current_stock: number;
    min_stock: number;
    percentage: number;
}

interface LowStockAlertsProps {
    items: LowStockItem[];
}

export default function LowStockAlerts({ items }: LowStockAlertsProps) {
    const getStatusColor = (percentage: number) => {
        if (percentage <= 25) return 'bg-red-500';
        if (percentage <= 50) return 'bg-orange-500';
        if (percentage <= 75) return 'bg-yellow-500';
        return 'bg-green-500';
    };

    const getUrgencyLevel = (percentage: number) => {
        if (percentage <= 25) return 'Critical';
        if (percentage <= 50) return 'High';
        if (percentage <= 75) return 'Medium';
        return 'Low';
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <AlertTriangle className="h-5 w-5 text-orange-600 dark:text-orange-400" />
                    Low Stock Alerts
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {items.length === 0 ? (
                        <div className="text-center py-6 text-muted-foreground">
                            <Package className="h-12 w-12 mx-auto mb-2 text-green-500 dark:text-green-400" />
                            <p>All items are well stocked!</p>
                        </div>
                    ) : (
                        items.map((item, index) => (
                            <div
                                key={index}
                                className="p-3 rounded-lg border border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-950/50"
                            >
                                <div className="flex items-center justify-between mb-2">
                                    <div className="flex-1">
                                        <div className="font-medium text-sm">
                                            {item.product_name}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            SKU: {item.product_sku}
                                        </div>
                                    </div>
                                    
                                    <div className="text-right">
                                        <div className="text-sm font-semibold">
                                            {item.current_stock} / {item.min_stock}
                                        </div>
                                        <div className={`text-xs px-2 py-1 rounded ${
                                            item.percentage <= 25 
                                                ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-200'
                                                : item.percentage <= 50
                                                ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-200'
                                                : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200'
                                        }`}>
                                            {getUrgencyLevel(item.percentage)}
                                        </div>
                                    </div>
                                </div>
                                
                                <div className="flex items-center gap-2 mb-2">
                                    <Warehouse className="h-4 w-4 text-muted-foreground" />
                                    <span className="text-sm text-muted-foreground">
                                        {item.warehouse_name}
                                    </span>
                                </div>
                                
                                <div className="space-y-1">
                                    <div className="flex justify-between text-xs">
                                        <span>Stock Level</span>
                                        <span>{item.percentage}%</span>
                                    </div>
                                    <Progress 
                                        value={item.percentage} 
                                        className="h-2"
                                        indicatorClassName={getStatusColor(item.percentage)}
                                    />
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
