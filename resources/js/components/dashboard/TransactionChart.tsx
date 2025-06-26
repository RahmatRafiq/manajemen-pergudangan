import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { BarChart3 } from 'lucide-react';

interface DailyTransaction {
    date: string;
    type: string;
    count: number;
    quantity: string;
}

interface TransactionChartProps {
    dailyData: Record<string, DailyTransaction[]>;
    stats: Record<string, { count: number; total_quantity: number }>;
}

export default function TransactionChart({ dailyData, stats }: TransactionChartProps) {
    const last14Days = Array.from({ length: 14 }, (_, i) => {
        const date = new Date();
        date.setDate(date.getDate() - (13 - i));
        return date.toISOString().split('T')[0];
    });

    const getTypeColor = (type: string) => {
        const colors = {
            in: '#10b981',      // green
            out: '#ef4444',     // red
            adjustment: '#3b82f6', // blue
            transfer: '#8b5cf6',   // purple
        };
        return colors[type as keyof typeof colors] || '#6b7280';
    };

    const getTypeLabel = (type: string) => {
        const labels = {
            in: 'Stock In',
            out: 'Stock Out',
            adjustment: 'Adjustment',
            transfer: 'Transfer',
        };
        return labels[type as keyof typeof labels] || type;
    };

    const maxQuantity = Math.max(
        ...last14Days.map(date => {
            const dayData = dailyData[date] || [];
            return dayData.reduce((sum, item) => sum + parseInt(item.quantity), 0);
        })
    );

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <BarChart3 className="h-5 w-5" />
                    Transaction Activity (Last 14 Days)
                </CardTitle>
            </CardHeader>
            <CardContent>
                {/* Summary Stats */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    {Object.entries(stats).map(([type, data]) => (
                        <div key={type} className="text-center p-3 rounded-lg border">
                            <div className="text-sm font-medium text-muted-foreground">
                                {getTypeLabel(type)}
                            </div>
                            <div className="text-xl font-bold" style={{ color: getTypeColor(type) }}>
                                {data.count}
                            </div>
                            <div className="text-xs text-muted-foreground">
                                {data.total_quantity} items
                            </div>
                        </div>
                    ))}
                </div>

                {/* Chart */}
                <div className="space-y-2">
                    <div className="flex justify-between text-sm text-muted-foreground mb-2">
                        <span>Daily Activity</span>
                        <span>Quantity Moved</span>
                    </div>
                    
                    <div className="grid grid-cols-7 md:grid-cols-14 gap-1">
                        {last14Days.map((date) => {
                            const dayData = dailyData[date] || [];
                            const totalQuantity = dayData.reduce((sum, item) => sum + parseInt(item.quantity), 0);
                            
                            return (
                                <div key={date} className="flex flex-col items-center">
                                    <div className="relative h-20 w-full mb-1">
                                        {dayData.length > 0 ? (
                                            <div className="absolute bottom-0 w-full space-y-0">
                                                {dayData.map((item, index) => {
                                                    const itemHeight = maxQuantity > 0 ? (parseInt(item.quantity) / maxQuantity) * 80 : 0;
                                                    return (
                                                        <div
                                                            key={index}
                                                            className="w-full rounded-sm"
                                                            style={{
                                                                height: `${itemHeight}px`,
                                                                backgroundColor: getTypeColor(item.type),
                                                                opacity: 0.8,
                                                            }}
                                                            title={`${getTypeLabel(item.type)}: ${item.quantity} items`}
                                                        />
                                                    );
                                                })}
                                            </div>
                                        ) : (
                                            <div className="absolute bottom-0 w-full h-1 bg-gray-100 rounded-sm" />
                                        )}
                                    </div>
                                    
                                    <div className="text-xs text-muted-foreground text-center">
                                        {new Date(date).toLocaleDateString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                        })}
                                    </div>
                                    
                                    {totalQuantity > 0 && (
                                        <div className="text-xs font-medium">
                                            {totalQuantity}
                                        </div>
                                    )}
                                </div>
                            );
                        })}
                    </div>
                </div>

                {/* Legend */}
                <div className="flex flex-wrap gap-4 mt-4 pt-4 border-t">
                    {Object.keys(stats).map((type) => (
                        <div key={type} className="flex items-center gap-2">
                            <div
                                className="w-3 h-3 rounded-sm"
                                style={{ backgroundColor: getTypeColor(type) }}
                            />
                            <span className="text-sm">{getTypeLabel(type)}</span>
                        </div>
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
