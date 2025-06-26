import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TrendingUp, TrendingDown, Minus, AlertTriangle } from 'lucide-react';

interface StatsCardProps {
    title: string;
    value: string | number;
    change?: {
        value: number;
        type: 'increase' | 'decrease' | 'neutral';
    };
    icon?: React.ReactNode;
    alert?: boolean;
}

export default function StatsCard({ title, value, change, icon, alert }: StatsCardProps) {
    const getChangeIcon = () => {
        if (!change) return null;
        
        switch (change.type) {
            case 'increase':
                return <TrendingUp className="h-4 w-4 text-green-600" />;
            case 'decrease':
                return <TrendingDown className="h-4 w-4 text-red-600" />;
            default:
                return <Minus className="h-4 w-4 text-gray-600" />;
        }
    };

    const getChangeColor = () => {
        if (!change) return '';
        
        switch (change.type) {
            case 'increase':
                return 'text-green-600';
            case 'decrease':
                return 'text-red-600';
            default:
                return 'text-gray-600';
        }
    };

    return (
        <Card className={alert ? 'border-orange-200 bg-orange-50' : ''}>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                    {title}
                </CardTitle>
                {alert ? (
                    <AlertTriangle className="h-4 w-4 text-orange-600" />
                ) : (
                    icon
                )}
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold">{value}</div>
                {change && (
                    <div className={`flex items-center text-xs ${getChangeColor()}`}>
                        {getChangeIcon()}
                        <span className="ml-1">
                            {Math.abs(change.value)}% from last month
                        </span>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
