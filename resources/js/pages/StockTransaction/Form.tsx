import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import AppLayout from '@/layouts/app-layout';
import CustomSelect from '@/components/select';
import { Inventory } from '@/types/Inventory';
import { StockTransaction } from '@/types/StockTransaction';

type Option = { value: number | string; label: string };

type Props = {
    transaction?: StockTransaction;
    inventories: Inventory[];
};

const typeOptions: Option[] = [
    { value: 'in', label: 'Stock In' },
    { value: 'out', label: 'Stock Out' },
    { value: 'adjustment', label: 'Adjustment' },
    { value: 'transfer', label: 'Transfer' },
];

export default function StockTransactionForm({ transaction, inventories }: Props) {
    const isEdit = !!transaction;

    const inventoryOptions: Option[] = inventories.map((inv) => ({
        value: inv.id,
        label: `${inv.product?.name ?? ''} (${inv.product?.sku ?? '-'}) @ ${inv.warehouse?.name ?? ''}`,
    }));

    const { data, setData, post, put, processing, errors } = useForm({
        inventory_id: transaction?.inventory_id || '',
        type: transaction?.type || 'in',
        quantity: transaction?.quantity || 0,
        reference: transaction?.reference || '',
        description: transaction?.description || '',
    });

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('stock-transaction.update', transaction!.id));
        } else {
            post(route('stock-transaction.store'));
        }
    };

    const selectedInventory =
        data.inventory_id
            ? inventoryOptions.find((opt) => opt.value === Number(data.inventory_id))!
            : null;
    const selectedType =
        typeOptions.find((opt) => opt.value === data.type) || typeOptions[0];

    return (
        <AppLayout>
            <Head title={isEdit ? 'Edit Stock Transaction' : 'Create Stock Transaction'} />
            <div className="px-4 py-6 max-w-2xl mx-auto">
                <h1 className="text-2xl font-semibold mb-4">
                    {isEdit ? 'Edit Stock Transaction' : 'Create Stock Transaction'}
                </h1>
                <form onSubmit={handleSubmit} className="space-y-4">
                    {/* Inventory */}
                    <div>
                        <Label htmlFor="inventory_id">Inventory (Product @ Warehouse)</Label>
                        <CustomSelect
                            id="inventory_id"
                            options={inventoryOptions}
                            value={selectedInventory}
                            placeholder="Select Inventory"
                            onChange={(opt) => {
                                if (opt && !Array.isArray(opt) && 'value' in opt) {
                                    setData('inventory_id', (opt as Option).value);
                                } else {
                                    setData('inventory_id', '');
                                }
                            }}
                        />
                        <InputError message={errors.inventory_id} />
                    </div>
                    {/* Type */}
                    <div>
                        <Label htmlFor="type">Type</Label>
                        <CustomSelect
                            id="type"
                            options={typeOptions}
                            value={selectedType}
                            placeholder="Select Type"
                            onChange={(opt) => {
                                if (opt && !Array.isArray(opt) && 'value' in opt) {
                                    setData('type', (opt as Option).value);
                                } else {
                                    setData('type', 'in');
                                }
                            }}
                        />
                        <InputError message={errors.type} />
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
                    {/* Reference */}
                    <div>
                        <Label htmlFor="reference">Reference</Label>
                        <Input
                            id="reference"
                            type="text"
                            value={data.reference}
                            onChange={(e) => setData('reference', e.target.value)}
                        />
                        <InputError message={errors.reference} />
                    </div>
                    {/* Description */}
                    <div>
                        <Label htmlFor="description">Description</Label>
                        <Input
                            id="description"
                            type="text"
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                        />
                        <InputError message={errors.description} />
                    </div>
                    {/* Actions */}
                    <div className="flex items-center space-x-4">
                        <Button disabled={processing}>
                            {isEdit ? 'Update Transaction' : 'Create Transaction'}
                        </Button>
                        <Link
                            href={route('stock-transaction.index')}
                            className="px-4 py-2 bg-muted text-foreground rounded hover:bg-muted/70"
                        >
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}