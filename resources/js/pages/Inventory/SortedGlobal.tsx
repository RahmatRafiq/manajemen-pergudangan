import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';

type InventoryGlobal = {
    product_id: number;
    total_quantity: number;
    product?: Product;
};

type Props = {
    inventories: InventoryGlobal[];
};

export default function SortedGlobal({ inventories }: Props) {
    return (
        <AppLayout>
            <Head title="Inventory Sorted Global" />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Inventory Sorted Global</h1>
                <Link href="/inventory" className="underline text-blue-600">Back to Inventory</Link>
                <table className="min-w-full mt-4 border">
                    <thead>
                        <tr>
                            <th className="border px-2 py-1">Product</th>
                            <th className="border px-2 py-1">SKU</th>
                            <th className="border px-2 py-1">Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        {inventories.map((inv) => (
                            <tr key={inv.product_id}>
                                <td className="border px-2 py-1">{inv.product?.name}</td>
                                <td className="border px-2 py-1">{inv.product?.sku}</td>
                                <td className="border px-2 py-1">{inv.total_quantity}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AppLayout>
    );
}