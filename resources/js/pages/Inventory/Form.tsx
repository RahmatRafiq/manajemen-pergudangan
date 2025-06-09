import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import CustomSelect from '@/components/select';
import { Inventory } from '@/types/Inventory';
import { Warehouse } from '@/types/Warehouse';

type Option = { value: number | string; label: string };

type Props = {
    inventory?: Inventory;
    products: Product[];
    warehouses: Warehouse[];
};

export default function InventoryForm({ inventory, products, warehouses }: Props) {
    const isEdit = !!inventory;

    const productOptions: Option[] = products.map((p) => ({
        value: p.id,
        label: p.name,
    }));
    const warehouseOptions: Option[] = warehouses.map((w) => ({
        value: w.id,
        label: w.name,
    }));

    const { data, setData, post, put, processing, errors } = useForm({
        warehouse_id: inventory?.warehouse_id || '',
        product_id: inventory?.product_id || '',
        quantity: inventory?.quantity || 0,
        reserved: inventory?.reserved || 0,
        min_stock: inventory?.min_stock || 0,
        max_stock: inventory?.max_stock || 0,
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Inventory Management', href: '/inventory' },
        { title: isEdit ? 'Edit Inventory' : 'Create Inventory', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('inventory.update', inventory!.id));
        } else {
            post(route('inventory.store'));
        }
    };

    const selectedWarehouse =
        data.warehouse_id
            ? warehouseOptions.find((opt) => opt.value === Number(data.warehouse_id))!
            : null;
    const selectedProduct =
        data.product_id
            ? productOptions.find((opt) => opt.value === Number(data.product_id))!
            : null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Inventory' : 'Create Inventory'} />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Inventory Management</h1>
                <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                    {/* Sidebar */}
                    <aside className="w-full max-w-xl lg:w-48">
                        <nav className="flex flex-col space-y-1">
                            <Button asChild variant="ghost" size="sm" className="justify-start">
                                <Link href="/inventory">Inventory List</Link>
                            </Button>
                            <Button asChild variant="ghost" size="sm" className="justify-start">
                                <Link href="/inventory/trashed">Trashed Inventory</Link>
                            </Button>
                        </nav>
                    </aside>

                    <div className="flex-1 md:max-w-2xl space-y-6">
                        <HeadingSmall
                            title={isEdit ? 'Edit Inventory' : 'Create Inventory'}
                            description="Isi data inventory di bawah ini"
                        />

                        <form onSubmit={handleSubmit} className="space-y-4">
                            {/* Warehouse */}
                            <div>
                                <Label htmlFor="warehouse_id">Warehouse</Label>
                                <CustomSelect
                                    id="warehouse_id"
                                    options={warehouseOptions}
                                    value={selectedWarehouse}
                                    placeholder="Pilih Gudang"
                                    onChange={(opt) => {
                                        if (opt && !Array.isArray(opt) && 'value' in opt) {
                                            setData('warehouse_id', (opt as Option).value);
                                        } else {
                                            setData('warehouse_id', '');
                                        }
                                    }}
                                />
                                <InputError message={errors.warehouse_id} />
                            </div>
                            {/* Product */}
                            <div>
                                <Label htmlFor="product_id">Product</Label>
                                <CustomSelect
                                    id="product_id"
                                    options={productOptions}
                                    value={selectedProduct}
                                    placeholder="Pilih Produk"
                                    onChange={(opt) => {
                                        if (opt && !Array.isArray(opt) && 'value' in opt) {
                                            setData('product_id', (opt as Option).value);
                                        } else {
                                            setData('product_id', '');
                                        }
                                    }}
                                />
                                <InputError message={errors.product_id} />
                            </div>
                            {/* Quantity */}
                            <div>
                                <Label htmlFor="quantity">Quantity</Label>
                                <Input
                                    id="quantity"
                                    type="number"
                                    value={data.quantity}
                                    onChange={(e) => setData('quantity', Number(e.target.value))}
                                    required
                                />
                                <InputError message={errors.quantity} />
                            </div>
                            {/* Reserved */}
                            <div>
                                <Label htmlFor="reserved">Reserved</Label>
                                <Input
                                    id="reserved"
                                    type="number"
                                    value={data.reserved}
                                    onChange={(e) => setData('reserved', Number(e.target.value))}
                                />
                                <InputError message={errors.reserved} />
                            </div>
                            {/* Min Stock */}
                            <div>
                                <Label htmlFor="min_stock">Min Stock</Label>
                                <Input
                                    id="min_stock"
                                    type="number"
                                    value={data.min_stock}
                                    onChange={(e) => setData('min_stock', Number(e.target.value))}
                                />
                                <InputError message={errors.min_stock} />
                            </div>
                            {/* Max Stock */}
                            <div>
                                <Label htmlFor="max_stock">Max Stock</Label>
                                <Input
                                    id="max_stock"
                                    type="number"
                                    value={data.max_stock}
                                    onChange={(e) => setData('max_stock', Number(e.target.value))}
                                />
                                <InputError message={errors.max_stock} />
                            </div>
                            {/* Actions */}
                            <div className="flex items-center space-x-4">
                                <Button disabled={processing}>
                                    {isEdit ? 'Update Inventory' : 'Create Inventory'}
                                </Button>
                                <Link
                                    href={route('inventory.index')}
                                    className="px-4 py-2 bg-muted text-foreground rounded hover:bg-muted/70"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}