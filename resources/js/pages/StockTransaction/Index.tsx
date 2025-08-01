import { useRef, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import DataTableWrapper, { DataTableWrapperRef } from '@/components/datatables';
import { StockTransaction } from '@/types/StockTransaction';
import clsx from 'clsx';

// Fungsi untuk render detail transaksi (harus di luar komponen agar tidak error TSX)
function formatTransactionDetails(data: any): string {
    return `
        <div class="p-4 bg-gray-50 border border-gray-200 rounded shadow-sm">
            <strong class="block text-gray-800 mb-2">Transaction Details:</strong>
            <ul class="ml-4">
                <li><b>Stock Sebelum Transaksi:</b> ${data.stock_before ?? '-'}</li>
                <li><b>Jenis Transaksi:</b> ${data.type === 'in' ? 'Stock In' : data.type === 'out' ? 'Stock Out' : data.type}</li>
                <li><b>Jumlah Transaksi:</b> ${data.quantity}</li>
                <li><b>Stock Aktual Setelah Transaksi:</b> ${data.stock_after ?? '-'}</li>
            </ul>
        </div>
    `;
}

const columns = (filter: string) => [
    {
        data: null,
        title: '',
        orderable: false,
        searchable: false,
        className: 'details-control',
        render: () => '<span style="cursor: pointer;">+</span>',
    },
    { data: 'id', title: 'ID' },
    { data: 'type', title: 'Type' },
    { data: 'warehouse', title: 'Warehouse' },
    { data: 'product', title: 'Product' },
    { data: 'sku', title: 'SKU' },
    { data: 'quantity', title: 'Quantity' },
    { data: 'reference', title: 'Reference' },
    { data: 'created_at', title: 'Created At' },
    {
        data: null,
        title: 'Actions',
        orderable: false,
        searchable: false,
        render: (_: null, __: string, row: unknown) => {
            const trx = row as StockTransaction;
            let html = '';
            if (filter === 'trashed' || (filter === 'all' && trx.trashed)) {
                html += `<button class="btn-restore ml-2 px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700" data-id="${trx.id}">Restore</button>`;
                html += `<button class="btn-force-delete ml-2 px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700" data-id="${trx.id}">Force Delete</button>`;
            } else {
                html += `<button class="btn-delete ml-2 px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700" data-id="${trx.id}">Delete</button>`;
            }
            return html;
        },
    },
];

export default function StockTransactionIndex({ filter: initialFilter, success }: { filter: string; success?: string }) {
    const dtRef = useRef<DataTableWrapperRef>(null);
    const [filter, setFilter] = useState(initialFilter || 'all');

    const handleDelete = (id: number) => {
        router.delete(route('stock-transaction.destroy', id), {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    const handleRestore = (id: number) => {
        router.post(route('stock-transaction.restore', id), {}, {
            onSuccess: () => dtRef.current?.reload(),
        });
    };

    const handleForceDelete = (id: number) => {
        router.delete(route('stock-transaction.force-delete', id), {
            onSuccess: () => dtRef.current?.reload(),
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
        <AppLayout>
            <Head title="Stock Transactions" />
            <div className="px-4 py-6">
                <h1 className="text-2xl font-semibold mb-4">Stock Transactions</h1>
                <div className="col-md-12">
                    <HeadingSmall title="Stock Transaction" description="Manage your stock transactions here." />
                    <div className="flex items-center justify-between mb-4">
                        <h2 className="text-xl font-semibold">Stock Transaction List</h2>
                        <Link href={route('stock-transaction.create')}>
                            <Button>Create Transaction</Button>
                        </Link>
                    </div>
                    <div className="mb-4">{renderToggleTabs()}</div>
                    {success && (
                        <div className="p-2 mb-2 bg-green-100 text-green-800 rounded">{success}</div>
                    )}
                    <DataTableWrapper
                        key={filter}
                        ref={dtRef}
                        ajax={{
                            url: route('stock-transaction.json') + '?filter=' + filter,
                            type: 'POST',
                        }}
                        columns={columns(filter)}
                        options={{
                            drawCallback: () => {
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

                                const table = dtRef.current?.dt();
                                if (table) {
                                    document.querySelectorAll('.details-control').forEach((cell) => {
                                        cell.addEventListener('click', function () {
                                            const tr = cell.closest('tr');
                                            if (!tr) return;
                                            const row = table.row(tr);
                                            if (row.child.isShown()) {
                                                row.child.hide();
                                                tr.classList.remove('shown');
                                                cell.innerHTML = '<span style="cursor: pointer;">+</span>';
                                            } else {
                                                row.child(formatTransactionDetails(row.data())).show();
                                                tr.classList.add('shown');
                                                cell.innerHTML = '<span style="cursor: pointer;">-</span>';
                                            }
                                        });
                                    });
                                }
                            }
                        }}
                    />
                </div>
            </div>
        </AppLayout>
    );
}