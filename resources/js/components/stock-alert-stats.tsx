import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { AlertTriangle, Package, Bell, TrendingUp } from 'lucide-react';

interface StockAlertStatsProps {
    totalAlerts: number;
    lowStockCount: number;
    overstockCount: number;
    unreadCount: number;
}

export function StockAlertStats({ totalAlerts, lowStockCount, overstockCount, unreadCount }: StockAlertStatsProps) {
    const stats = [
        {
            title: 'Total Alerts',
            value: totalAlerts,
            icon: Bell,
            color: 'text-blue-600 dark:text-blue-400',
            bgColor: 'bg-blue-100 dark:bg-blue-900/20',
            description: 'All stock alerts',
        },
        {
            title: 'Low Stock',
            value: lowStockCount,
            icon: AlertTriangle,
            color: 'text-red-600 dark:text-red-400',
            bgColor: 'bg-red-100 dark:bg-red-900/20',
            description: 'Items below minimum',
        },
        {
            title: 'Overstock',
            value: overstockCount,
            icon: Package,
            color: 'text-orange-600 dark:text-orange-400',
            bgColor: 'bg-orange-100 dark:bg-orange-900/20',
            description: 'Items above maximum',
        },
        {
            title: 'Unread',
            value: unreadCount,
            icon: TrendingUp,
            color: 'text-purple-600 dark:text-purple-400',
            bgColor: 'bg-purple-100 dark:bg-purple-900/20',
            description: 'New notifications',
        },
    ];

    return (
        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            {stats.map((stat) => {
                const Icon = stat.icon;
                return (
                    <Card key={stat.title} className="relative overflow-hidden">
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">
                                {stat.title}
                            </CardTitle>
                            <div className={`flex h-8 w-8 items-center justify-center rounded-lg ${stat.bgColor}`}>
                                <Icon className={`h-4 w-4 ${stat.color}`} />
                            </div>
                        </CardHeader>
                        <CardContent>
                            <div className="flex items-center justify-between">
                                <div>
                                    <div className="text-2xl font-bold">{stat.value}</div>
                                    <p className="text-xs text-muted-foreground">
                                        {stat.description}
                                    </p>
                                </div>
                                {stat.value > 0 && stat.title === 'Unread' && (
                                    <Badge variant="destructive" className="text-xs">
                                        New
                                    </Badge>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                );
            })}
        </div>
    );
}
