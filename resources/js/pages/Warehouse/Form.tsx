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
import { Warehouse } from '@/types/Warehouse';

export default function WarehouseForm({
    warehouse,
    regionOptions = [],
}: {
    warehouse?: Warehouse,
    regionOptions?: { value: number, label: string }[]
}) {
    const isEdit = !!warehouse;

    const { data, setData, post, put, processing, errors } = useForm({
        region_id: warehouse?.region_id || '',
        name: warehouse?.name || '',
        address: warehouse?.address || '',
        phone: warehouse?.phone || '',
        manager: warehouse?.manager || '',
    });

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Warehouse Management', href: '/warehouse' },
        { title: isEdit ? 'Edit Warehouse' : 'Create Warehouse', href: '#' },
    ];

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit) {
            put(route('warehouse.update', warehouse!.id));
        } else {
            post(route('warehouse.store'));
        }
    };

    const selectedRegion = regionOptions.find(opt => opt.value === data.region_id) || null;

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={isEdit ? 'Edit Warehouse' : 'Create Warehouse'} />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Warehouse Management</h1>
                <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                    <aside className="w-full max-w-xl lg:w-48">
                        <nav className="flex flex-col space-y-1">
                            <Button asChild variant="ghost" size="sm" className="justify-start">
                                <Link href="/warehouse">Warehouse List</Link>
                            </Button>
                            <Button asChild variant="ghost" size="sm" className="justify-start">
                                <Link href="/warehouse/trashed">Trashed warehouse</Link>
                            </Button>
                        </nav>
                    </aside>
                    <div className="flex-1 md:max-w-2xl space-y-6">
                        <HeadingSmall
                            title={isEdit ? 'Edit Warehouse' : 'Create Warehouse'}
                            description="Isi data gudang di bawah ini"
                        />

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <Label htmlFor="region_id">Region</Label>
                                <CustomSelect
                                    id="region_id"
                                    options={regionOptions}
                                    value={selectedRegion}
                                    placeholder="Pilih region"
                                    onChange={opt => {
                                        if (opt && !Array.isArray(opt) && typeof opt === 'object' && 'value' in opt) {
                                            setData('region_id', opt.value);
                                        } else {
                                            setData('region_id', '');
                                        }
                                    }}
                                />
                                <InputError message={errors.region_id} />
                            </div>
                            <div>
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={e => setData('name', e.target.value)}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>
                            <div>
                                <Label htmlFor="address">Address</Label>
                                <Input
                                    id="address"
                                    type="text"
                                    value={data.address}
                                    onChange={e => setData('address', e.target.value)}
                                />
                                <InputError message={errors.address} />
                            </div>
                            <div>
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    value={data.phone}
                                    onChange={e => setData('phone', e.target.value)}
                                />
                                <InputError message={errors.phone} />
                            </div>
                            <div>
                                <Label htmlFor="manager">Manager</Label>
                                <Input
                                    id="manager"
                                    type="text"
                                    value={data.manager}
                                    onChange={e => setData('manager', e.target.value)}
                                />
                                <InputError message={errors.manager} />
                            </div>
                            <div className="flex items-center space-x-4">
                                <Button disabled={processing}>
                                    {isEdit ? 'Update Warehouse' : 'Create Warehouse'}
                                </Button>
                                <Link
                                    href={route('warehouse.index')}
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