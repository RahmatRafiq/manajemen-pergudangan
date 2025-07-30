import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Clock, User, Package, ArrowRight } from 'lucide-react';
import { Link } from '@inertiajs/react';

interface Transaction {
    id: number;
    type: string;
    quantity: number;
    reference: string;
    created_at: string;
    inventory: {
        product: {
            name: string;
            sku: string;
        };
        warehouse: {
            name: string;
        };
    };
    creator: {
        name: string;
    };
}

interface RecentTransactionsProps {
    transactions: Transaction[];
}

const typeColors = {
    in: 'bg-green-100 text-green-800',
    out: 'bg-red-100 text-red-800',
    adjustment: 'bg-blue-100 text-blue-800',
    transfer: 'bg-purple-100 text-purple-800',
};

const typeLabels = {
    in: 'Stock In',
    out: 'Stock Out',
    adjustment: 'Adjustment',
    transfer: 'Transfer',
};

export default function RecentTransactions({ transactions }: RecentTransactionsProps) {
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <Card>
            <CardHeader>
                <div className="flex items-center justify-between">
                    <CardTitle className="flex items-center gap-2">
                        <Clock className="h-5 w-5" />
                        Recent Transactions
                    </CardTitle>
                    <Link href="/stock-transaction">
                        <Button variant="outline" size="sm">
                            View All
                            <ArrowRight className="h-4 w-4 ml-2" />
                        </Button>
                    </Link>
                </div>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {transactions.length === 0 ? (
                        <div className="text-center py-6 text-muted-foreground">
                            No recent transactions
                        </div>
                    ) : (
                        transactions.slice(0, 5).map((transaction) => {
                            const product = transaction.inventory?.product ?? null;
                            const warehouse = transaction.inventory?.warehouse ?? null;
                            const creator = transaction.creator ?? null;
                            return (
                                <div
                                    key={transaction.id}
                                    className="flex flex-col gap-3 p-3 rounded-lg border"
                                >
                                    {/* Header Row */}
                                    <div className="flex items-center justify-between">
                                        <div className="flex items-center gap-2">
                                            <Badge className={typeColors[transaction.type as keyof typeof typeColors]}>
                                                {typeLabels[transaction.type as keyof typeof typeLabels]}
                                            </Badge>
                                            <span className="text-sm font-medium">
                                                {transaction.reference}
                                            </span>
                                        </div>
                                        <div className={`text-lg font-semibold ${
                                            transaction.type === 'in' || transaction.type === 'adjustment'
                                                ? 'text-green-600' 
                                                : 'text-red-600'
                                        }`}>
                                            {transaction.type === 'out' ? '-' : '+'}
                                            {transaction.quantity}
                                        </div>
                                    </div>
                                    
                                    {/* Product Info */}
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Package className="h-4 w-4" />
                                        <span>
                                            {product ? `${product.name} (${product.sku})` : 'Unknown Product'}
                                        </span>
                                    </div>
                                    
                                    {/* Footer Row */}
                                    <div className="flex items-center justify-between text-xs text-muted-foreground">
                                        <span>@ {warehouse ? warehouse.name : 'Unknown Warehouse'}</span>
                                        <div className="flex items-center gap-3">
                                            <div className="flex items-center gap-1">
                                                <User className="h-3 w-3" />
                                                <span>{creator ? creator.name : 'Unknown User'}</span>
                                            </div>
                                            <span>{formatDate(transaction.created_at)}</span>
                                        </div>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>
            </CardContent>
        </Card>
    );
}