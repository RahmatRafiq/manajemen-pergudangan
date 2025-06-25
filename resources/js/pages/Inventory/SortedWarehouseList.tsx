import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Warehouse, Package, BarChart3 } from 'lucide-react';

type WarehouseData = {
    id: number;
    name: string;
    address?: string;
    total_inventory?: number;
};

type Props = {
    warehouses: WarehouseData[];
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Inventory Management', href: '/inventory' },
    { title: 'Sorted by Warehouse', href: '/inventory/sorted-warehouse' },
];

export default function SortedWarehouseList({ warehouses }: Props) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inventory Sorted by Warehouse" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6">
                <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-center gap-3">
                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/20">
                            <Warehouse className="h-5 w-5 text-blue-600 dark:text-blue-400" />
                        </div>
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                Inventory Sorted by Warehouse
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                Select a warehouse to view its inventory
                            </p>
                        </div>
                    </div>
                    <div className="flex items-center gap-2">
                        <Button variant="outline" asChild>
                            <Link href="/inventory/sorted-global">
                                <BarChart3 className="h-4 w-4 mr-2" />
                                View Global Inventory
                            </Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    {warehouses.length === 0 ? (
                        <div className="col-span-full">
                            <Card>
                                <CardContent className="flex flex-col items-center justify-center py-12">
                                    <Warehouse className="h-12 w-12 text-muted-foreground mb-4" />
                                    <p className="text-lg font-medium text-muted-foreground">
                                        No warehouses found
                                    </p>
                                    <p className="text-sm text-muted-foreground mb-4">
                                        Create a warehouse to start managing inventory
                                    </p>
                                    <Button asChild>
                                        <Link href="/warehouse">
                                            Manage Warehouses
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        </div>
                    ) : (
                        warehouses.map((warehouse) => (
                            <Card key={warehouse.id} className="hover:shadow-md transition-shadow">
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Warehouse className="h-5 w-5 text-blue-600" />
                                        {warehouse.name}
                                    </CardTitle>
                                    {warehouse.address && (
                                        <CardDescription>{warehouse.address}</CardDescription>
                                    )}
                                </CardHeader>
                                <CardContent>
                                    <div className="flex items-center justify-between mb-4">
                                        <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                            <Package className="h-4 w-4" />
                                            <span>
                                                {warehouse.total_inventory || 0} items
                                            </span>
                                        </div>
                                    </div>
                                    <Button asChild className="w-full">
                                        <Link href={`/inventory/warehouse/${warehouse.id}`}>
                                            View Inventory
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        ))
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
