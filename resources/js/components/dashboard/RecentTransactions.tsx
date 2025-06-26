import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Clock, User, Package } from 'lucide-react';

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
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <Clock className="h-5 w-5" />
                    Recent Transactions
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {transactions.length === 0 ? (
                        <div className="text-center py-6 text-muted-foreground">
                            No recent transactions
                        </div>
                    ) : (
                        transactions.map((transaction) => (
                            <div
                                key={transaction.id}
                                className="flex items-center justify-between p-3 rounded-lg border"
                            >
                                <div className="flex-1">
                                    <div className="flex items-center gap-2 mb-1">
                                        <Badge className={typeColors[transaction.type as keyof typeof typeColors]}>
                                            {typeLabels[transaction.type as keyof typeof typeLabels]}
                                        </Badge>
                                        <span className="text-sm font-medium">
                                            {transaction.reference}
                                        </span>
                                    </div>
                                    
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Package className="h-4 w-4" />
                                        <span>
                                            {transaction.inventory.product.name} ({transaction.inventory.product.sku})
                                        </span>
                                    </div>
                                    
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <span>@ {transaction.inventory.warehouse.name}</span>
                                    </div>
                                </div>
                                
                                <div className="text-right">
                                    <div className={`text-lg font-semibold ${
                                        transaction.type === 'in' || transaction.type === 'adjustment'
                                            ? 'text-green-600' 
                                            : 'text-red-600'
                                    }`}>
                                        {transaction.type === 'out' ? '-' : '+'}
                                        {transaction.quantity}
                                    </div>
                                    
                                    <div className="flex items-center gap-1 text-xs text-muted-foreground">
                                        <User className="h-3 w-3" />
                                        <span>{transaction.creator.name}</span>
                                    </div>
                                    
                                    <div className="text-xs text-muted-foreground">
                                        {formatDate(transaction.created_at)}
                                    </div>
                                </div>
                            </div>
                        ))
                    )}
                </div>
            </CardContent>
        </Card>
    );
}
