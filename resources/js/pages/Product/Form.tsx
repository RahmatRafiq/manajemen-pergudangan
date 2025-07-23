import { Head, useForm, Link } from '@inertiajs/react';
import { FormEvent, useEffect } from 'react';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/app-layout';
import CustomSelect from '@/components/select';

type Option = { value: number | string; label: string };

type Props = {
    product?: {
        id: number;
        sku: string;
        name: string;
        vendor?: string;
        category_id: number | null;
        unit: string;
        description: string;
        warehouse_id?: number | null;
    };
    categoryOptions: Option[];
    warehouseOptions: Option[];
};

export default function ProductForm({ product, categoryOptions, warehouseOptions = [] }: Props) {
    const isEdit = !!product;

    const { data, setData, post, put, processing, errors } = useForm({
        sku: product?.sku || '',
        name: product?.name || '',
        vendor: product?.vendor || '',
        category_id: product?.category_id || '',
        unit: product?.unit || '',
        description: product?.description || '',
        warehouse_id: product?.warehouse_id || '',
    });

    useEffect(() => {
        if (!isEdit && warehouseOptions.length === 1) {
            setData('warehouse_id', warehouseOptions[0].value);
        }
    }, [warehouseOptions, isEdit, setData]);

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Product Management', href: '/products' },
        { title: isEdit ? 'Edit Product' : 'Create Product', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('product.update', product!.id));
        } else {
            post(route('product.store'));
        }
    };

    const selectedCategory =
        data.category_id
            ? categoryOptions.find((opt) => opt.value === Number(data.category_id))!
            : null;

    const selectedWarehouse =
        data.warehouse_id
            ? warehouseOptions.find((opt) => opt.value === Number(data.warehouse_id)) || null
            : null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Product' : 'Create Product'} />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Product Management</h1>
                <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                    <aside className="w-full max-w-xl lg:w-48">
                        <nav className="flex flex-col space-y-1">
                            <Button asChild variant="ghost" size="sm" className="justify-start">
                                <Link href="/product">Product List</Link>
                            </Button>
                            <Button asChild variant="ghost" size="sm" className="justify-start">
                                <Link href="/product/trashed">Trashed Products</Link>
                            </Button>
                        </nav>
                    </aside>

                    <div className="flex-1 md:max-w-2xl space-y-6">
                        <HeadingSmall
                            title={isEdit ? 'Edit Product' : 'Create Product'}
                            description="Fill in the product details below"
                        />

                        <form onSubmit={handleSubmit} className="space-y-4">
                            {isEdit && (
                                <div>
                                    <Label htmlFor="sku">SKU</Label>
                                    <Input
                                        id="sku"
                                        type="text"
                                        value={data.sku}
                                        readOnly
                                    />
                                    <InputError message={errors.sku} />
                                </div>
                            )}

                            <div>
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div>
                                <Label htmlFor="vendor">Vendor</Label>
                                <Input
                                    id="vendor"
                                    type="text"
                                    value={data.vendor}
                                    onChange={(e) => setData('vendor', e.target.value)}
                                    placeholder="Enter vendor name"
                                />
                                <InputError message={errors.vendor} />
                            </div>

                            <div>
                                <Label htmlFor="category_id">Category</Label>
                                <CustomSelect
                                    id="category_id"
                                    options={categoryOptions}
                                    value={selectedCategory}
                                    placeholder="Select category"
                                    onChange={(opt) => {
                                        if (opt && !Array.isArray(opt) && 'value' in opt) {
                                            setData('category_id', (opt as Option).value);
                                        } else {
                                            setData('category_id', '');
                                        }
                                    }}
                                />
                                <InputError message={errors.category_id} />
                            </div>

                            <div>
                                <Label htmlFor="unit">Unit</Label>
                                <CustomSelect
                                    id="unit"
                                    options={[
                                        { value: 'pcs', label: 'pcs' },
                                        { value: 'box', label: 'box' },
                                        { value: 'kg', label: 'kg' },
                                        { value: 'ltr', label: 'ltr' },
                                    ]}
                                    value={
                                        data.unit ? { value: data.unit, label: data.unit } : null
                                    }
                                    placeholder="Select unit"
                                    onChange={(opt) => {
                                        if (opt && !Array.isArray(opt) && 'value' in opt) {
                                            setData('unit', String((opt as Option).value));
                                        } else {
                                            setData('unit', '');
                                        }
                                    }}
                                />
                                <InputError message={errors.unit} />
                            </div>

                            <div>
                                <Label htmlFor="description">Description</Label>
                                <Input
                                    id="description"
                                    type="text"
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    placeholder="Enter description"
                                />
                                <InputError message={errors.description} />
                            </div>

                            {warehouseOptions.length > 1 && (
                                <div>
                                    <Label htmlFor="warehouse_id">Warehouse</Label>
                                    <CustomSelect
                                        id="warehouse_id"
                                        options={warehouseOptions}
                                        value={selectedWarehouse}
                                        placeholder="Select warehouse"
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
                            )}
                            {warehouseOptions.length === 1 && (
                                <div className="text-sm text-gray-500">
                                    Produk akan otomatis dicatat di warehouse: <b>{warehouseOptions[0].label}</b>
                                </div>
                            )}

                            <div className="flex items-center space-x-4">
                                <Button disabled={processing}>
                                    {isEdit ? 'Update Product' : 'Create Product'}
                                </Button>
                                <Link
                                    href={route('product.index')}
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
