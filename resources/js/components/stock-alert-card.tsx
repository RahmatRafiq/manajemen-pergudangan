import { useState } from 'react';
import { type StockAlert } from '@/types/StockAlert';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { AlertTriangle, Package, CheckCircle, Clock, Building, Hash } from 'lucide-react';
import { formatDistanceToNow } from 'date-fns';

interface StockAlertCardProps {
    alert: StockAlert;
    onMarkAsRead?: () => Promise<void>;
}

export function StockAlertCard({ alert, onMarkAsRead }: StockAlertCardProps) {
    const [isMarkingAsRead, setIsMarkingAsRead] = useState(false);

    const handleMarkAsRead = async () => {
        if (!onMarkAsRead) return;
        
        setIsMarkingAsRead(true);
        try {
            await onMarkAsRead();
        } catch (error) {
            console.error('Error marking alert as read:', error);
        } finally {
            setIsMarkingAsRead(false);
        }
    };

    const isLowStock = alert.type === 'low_stock';
    const timestamp = alert.timestamp || alert.created_at;
    const timeAgo = timestamp ? formatDistanceToNow(new Date(timestamp), { addSuffix: true }) : 'Unknown time';

    return (
        <Card className={`transition-all duration-200 hover:shadow-md ${
            isLowStock 
                ? 'border-l-4 border-l-red-500 bg-red-50/50 dark:bg-red-950/20' 
                : 'border-l-4 border-l-orange-500 bg-orange-50/50 dark:bg-orange-950/20'
        }`}>
            <CardHeader className="pb-3">
                <div className="flex items-start justify-between">
                    <div className="flex items-start gap-3">
                        <div className={`flex h-10 w-10 items-center justify-center rounded-full ${
                            isLowStock 
                                ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400'
                                : 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400'
                        }`}>
                            {isLowStock ? (
                                <AlertTriangle className="h-5 w-5" />
                            ) : (
                                <Package className="h-5 w-5" />
                            )}
                        </div>
                        
                        <div className="min-w-0 flex-1">
                            <div className="flex items-center gap-2">
                                <Badge 
                                    variant={isLowStock ? 'destructive' : 'secondary'}
                                    className="text-xs"
                                >
                                    {isLowStock ? 'Low Stock' : 'Overstock'}
                                </Badge>
                                <span className="text-xs text-muted-foreground flex items-center gap-1">
                                    <Clock className="h-3 w-3" />
                                    {timeAgo}
                                </span>
                            </div>
                            
                            <h3 className="mt-1 font-medium text-sm leading-tight">
                                {alert.product_name}
                            </h3>
                            
                            <p className="mt-1 text-sm text-muted-foreground">
                                {alert.message}
                            </p>
                        </div>
                    </div>

                    {onMarkAsRead && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={handleMarkAsRead}
                            disabled={isMarkingAsRead}
                            className="shrink-0"
                        >
                            <CheckCircle className="h-4 w-4" />
                        </Button>
                    )}
                </div>
            </CardHeader>

            <CardContent className="pt-0">
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <div className="flex items-center gap-2 text-sm">
                        <Building className="h-4 w-4 text-muted-foreground" />
                        <div>
                            <span className="font-medium">Warehouse:</span>
                            <p className="text-muted-foreground">{alert.warehouse_name}</p>
                        </div>
                    </div>

                    <div className="flex items-center gap-2 text-sm">
                        <Package className="h-4 w-4 text-muted-foreground" />
                        <div>
                            <span className="font-medium">Current Stock:</span>
                            <p className={`font-mono ${
                                isLowStock ? 'text-red-600 dark:text-red-400' : 'text-orange-600 dark:text-orange-400'
                            }`}>
                                {alert.current_quantity}
                            </p>
                        </div>
                    </div>

                    {alert.min_stock !== null && (
                        <div className="flex items-center gap-2 text-sm">
                            <Hash className="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span className="font-medium">Min Stock:</span>
                                <p className="font-mono text-muted-foreground">{alert.min_stock}</p>
                            </div>
                        </div>
                    )}

                    {alert.max_stock !== null && (
                        <div className="flex items-center gap-2 text-sm">
                            <Hash className="h-4 w-4 text-muted-foreground" />
                            <div>
                                <span className="font-medium">Max Stock:</span>
                                <p className="font-mono text-muted-foreground">{alert.max_stock}</p>
                            </div>
                        </div>
                    )}
                </div>

                {/* Progress bar for stock level visualization */}
                <div className="mt-4">
                    <div className="flex justify-between text-xs text-muted-foreground mb-1">
                        <span>Stock Level</span>
                        <span>{alert.current_quantity} units</span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                        <div 
                            className={`h-2 rounded-full transition-all duration-300 ${
                                isLowStock 
                                    ? 'bg-red-500' 
                                    : 'bg-orange-500'
                            }`}
                            style={{
                                width: `${Math.min(100, Math.max(5, 
                                    alert.max_stock 
                                        ? (alert.current_quantity / alert.max_stock) * 100
                                        : alert.min_stock
                                            ? Math.min(100, (alert.current_quantity / (alert.min_stock * 2)) * 100)
                                            : 50
                                ))}%`
                            }}
                        />
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}
