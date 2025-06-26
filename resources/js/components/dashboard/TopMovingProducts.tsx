import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TrendingUp, Package, Warehouse } from 'lucide-react';

interface TopMovingProduct {
    product_name: string;
    product_sku: string;
    warehouse_name: string;
    total_movement: number;
    transaction_count: number;
    current_stock: number;
}

interface TopMovingProductsProps {
    products: TopMovingProduct[];
}

export default function TopMovingProducts({ products }: TopMovingProductsProps) {
    const maxMovement = Math.max(...products.map(p => p.total_movement), 1);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <TrendingUp className="h-5 w-5" />
                    Top Moving Products (Last 30 Days)
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-3">
                    {products.length === 0 ? (
                        <div className="text-center py-6 text-muted-foreground">
                            <Package className="h-12 w-12 mx-auto mb-2" />
                            <p>No product movement data available</p>
                        </div>
                    ) : (
                        products.map((product, index) => {
                            const movementPercentage = (product.total_movement / maxMovement) * 100;
                            
                            return (
                                <div
                                    key={index}
                                    className="flex items-center justify-between p-3 rounded-lg border hover:bg-gray-50 transition-colors"
                                >
                                    <div className="flex-1">
                                        <div className="flex items-center gap-2">
                                            <div className="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">
                                                {index + 1}
                                            </div>
                                            <div>
                                                <div className="font-medium text-sm">
                                                    {product.product_name}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    SKU: {product.product_sku}
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div className="flex items-center gap-2 mt-1 ml-8">
                                            <Warehouse className="h-3 w-3 text-muted-foreground" />
                                            <span className="text-xs text-muted-foreground">
                                                {product.warehouse_name}
                                            </span>
                                        </div>
                                        
                                        {/* Movement Bar */}
                                        <div className="ml-8 mt-2">
                                            <div className="flex justify-between text-xs mb-1">
                                                <span>Movement Activity</span>
                                                <span>{product.total_movement} units</span>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                <div
                                                    className="bg-blue-600 h-2 rounded-full transition-all"
                                                    style={{ width: `${movementPercentage}%` }}
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div className="text-right ml-4">
                                        <div className="text-lg font-bold text-blue-600">
                                            {product.total_movement}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {product.transaction_count} transactions
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            Stock: {product.current_stock}
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
