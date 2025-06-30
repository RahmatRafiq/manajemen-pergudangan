import { useRef, useState } from 'react';
import ReactDOM from 'react-dom/client';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import DataTableWrapper, { DataTableWrapperRef } from '@/components/datatables';
import { BreadcrumbItem } from '@/types';
import clsx from 'clsx';
import { Inventory } from '@/types/Inventory';

const columns = (filter: string) => [
    { data: 'id', title: 'ID' },
    { data: 'warehouse', title: 'Warehouse' },
    { data: 'product', title: 'Product' },
    { data: 'sku', title: 'SKU' },
    { data: 'quantity', title: 'Quantity' },
    // { data: 'reserved', title: 'Reserved' },
    { data: 'min_stock', title: 'Min Stock' },
    { data: 'max_stock', title: 'Max Stock' },
    { data: 'updated_at', title: 'Updated At' },
    {
        data: null,
        title: 'Actions',
        orderable: false,
        searchable: false,
        render: (_: null, __: string, row: unknown) => {
            const inventory = row as Inventory;
            let html = '';
            if (filter === 'trashed' || (filter === 'all' && inventory.trashed)) {
                html += `<button class="btn-restore ml-2 px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700" data-id="${inventory.id}">Restore</button>`;
                html += `<button class="btn-force-delete ml-2 px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700" data-id="${inventory.id}">Force Delete</button>`;
            } else {
                html += `<span class="inertia-link-cell" data-id="${inventory.id}"></span>`;
                html += `<button class="btn-delete ml-2 px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700" data-id="${inventory.id}">Delete</button>`;
            }
            return html;
        },
    },
];

export default function InventoryIndex({ filter: initialFilter, success }: { filter: string; success?: string }) {
    const breadcrumbs: BreadcrumbItem[] = [{ title: 'Inventory Management', href: '/inventory' }];
    const dtRef = useRef<DataTableWrapperRef>(null);
    const [filter, setFilter] = useState(initialFilter || 'active');

    const handleDelete = (id: number) => {
        router.delete(route('inventory.destroy', id), {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    const handleRestore = (id: number) => {
        router.post(route('inventory.restore', id), {}, {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    const handleForceDelete = (id: number) => {
        router.delete(route('inventory.force-delete', id), {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    const drawCallback = () => {
        // Apply row coloring based on stock status
        const dataTable = dtRef.current?.dt();
        if (dataTable) {
            document.querySelectorAll('table tbody tr').forEach((row, index) => {
                const rowData = dataTable.row(index).data();
                const htmlRow = row as HTMLElement;
                if (rowData) {
                    // Debug: Log the row data to see what we're getting
                    if (index === 0) {
                        console.log('Sample row data:', rowData);
                    }
                    
                    // Remove existing classes
                    htmlRow.classList.remove('bg-red-50', 'bg-blue-50', 'dark:bg-red-950/50', 'dark:bg-blue-950/50', 'low-stock-row', 'overstock-row');
                    
                    // Add classes based on stock status
                    if (rowData.is_low_stock === true) {
                        htmlRow.classList.add('low-stock-row');
                        console.log('Applied low-stock-row to row', index);
                    } else if (rowData.is_overstock === true) {
                        htmlRow.classList.add('overstock-row');
                        console.log('Applied overstock-row to row', index);
                    }
                }
            });
        }

        document.querySelectorAll('.inertia-link-cell').forEach((cell) => {
            const id = cell.getAttribute('data-id');
            if (id) {
                const root = ReactDOM.createRoot(cell);
                root.render(
                    <Link
                        href={`/inventory/${id}/edit`}
                        className="inline-block ml-2 px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-center"
                    >
                        Edit
                    </Link>
                );
            }
        });

        document.querySelectorAll('.btn-delete').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (id) handleDelete(Number(id));
            });
        });
        document.querySelectorAll('.btn-restore').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (id) handleRestore(Number(id));
            });
        });
        document.querySelectorAll('.btn-force-delete').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.getAttribute('data-id');
                if (id) handleForceDelete(Number(id));
            });
        });
    };

    const renderToggleTabs = () => {
        const tabs = ['active', 'trashed', 'all'];
        return (
            <div className="inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
                {tabs.map((tab) => (
                    <button
                        key={tab}
                        onClick={() => setFilter(tab)}
                        className={clsx(
                            'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                            filter === tab
                                ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60'
                        )}
                    >
                        {tab.charAt(0).toUpperCase() + tab.slice(1)}
                    </button>
                ))}
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Inventory Management" />
            <style>{`
                .low-stock-row {
                    background-color: rgba(254, 226, 226, 0.5) !important;
                }
                .overstock-row {
                    background-color: rgba(219, 234, 254, 0.5) !important;
                }
                .low-stock-row:hover {
                    background-color: rgba(254, 226, 226, 0.7) !important;
                }
                .overstock-row:hover {
                    background-color: rgba(219, 234, 254, 0.7) !important;
                }
                @media (prefers-color-scheme: dark) {
                    .low-stock-row {
                        background-color: rgba(127, 29, 29, 0.3) !important;
                    }
                    .overstock-row {
                        background-color: rgba(30, 58, 138, 0.3) !important;
                    }
                }
            `}</style>
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Inventory Management</h1>
                <div className="col-md-12">
                    <HeadingSmall title="Inventory" description="Manage your inventory here." />
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-xl font-semibold">Inventory List</h2>
                        <Link href={route('inventory.create')}>
                            <Button>Create Inventory</Button>
                        </Link>
                    </div>
                    <div className="mb-4">{renderToggleTabs()}</div>
                    
                    {/* Stock Status Legend */}
                    <div className="mb-4 p-3 bg-gray-50 dark:bg-gray-900 rounded-lg">
                        <h4 className="text-sm font-medium mb-2">Stock Status Legend:</h4>
                        <div className="flex flex-wrap gap-4 text-xs">
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded"></div>
                                <span>Low Stock</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 bg-blue-50 dark:bg-blue-950/50 border border-blue-200 dark:border-blue-800 rounded"></div>
                                <span>Overstock</span>
                            </div>
                            <div className="flex items-center gap-2">
                                <div className="w-4 h-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded"></div>
                                <span>Normal Stock</span>
                            </div>
                        </div>
                    </div>
                    {success && (
                        <div className="p-2 mb-2 bg-green-100 text-green-800 rounded">{success}</div>
                    )}
                    <DataTableWrapper
                        key={filter}
                        ref={dtRef}
                        ajax={{
                            url: route('inventory.json') + '?filter=' + filter,
                            type: 'POST',
                        }}
                        columns={columns(filter)}
                        options={{ drawCallback }}
                    />
                </div>
            </div>
        </AppLayout>
    );
}