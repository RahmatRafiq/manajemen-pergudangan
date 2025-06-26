import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { useState, useMemo } from 'react';

type Product = {
    id: number;
    name: string;
    sku: string;
};

type Warehouse = {
    id: number;
    name: string;
};

type InventoryGlobal = {
    product_id: number;
    warehouse_id: number;
    total_quantity: number;
    total_movement: number;
    transaction_count: number;
    movement_category: 'no_movement' | 'low_movement' | 'medium_movement' | 'high_movement';
    movement_ratio: number;
    recommendation: {
        status: 'danger' | 'warning' | 'info' | 'success';
        text: string;
        action: string;
    };
    product?: Product;
    warehouse?: Warehouse;
};

type Props = {
    inventories: InventoryGlobal[];
    period: string;
    periods: Record<string, string>;
};

export default function SortedGlobal({ inventories, period, periods }: Props) {
    const [movementFilter, setMovementFilter] = useState<string>('all');
    const [sortBy, setSortBy] = useState<'movement' | 'quantity' | 'ratio'>('movement');
    const [sortOrder, setSortOrder] = useState<'asc' | 'desc'>('asc');

    const handlePeriodChange = (newPeriod: string) => {
        router.get('/inventory/sorted/global', { period: newPeriod }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Filter dan sort data secara client-side
    const filteredAndSortedInventories = useMemo(() => {
        let filtered = inventories;

        // Apply movement filter
        if (movementFilter !== 'all') {
            filtered = filtered.filter(inv => inv.movement_category === movementFilter);
        }

        // Apply sorting
        filtered = [...filtered].sort((a, b) => {
            let aValue, bValue;
            
            switch (sortBy) {
                case 'movement':
                    aValue = a.total_movement || 0;
                    bValue = b.total_movement || 0;
                    break;
                case 'quantity':
                    aValue = a.total_quantity || 0;
                    bValue = b.total_quantity || 0;
                    break;
                case 'ratio':
                    aValue = a.movement_ratio || 0;
                    bValue = b.movement_ratio || 0;
                    break;
                default:
                    aValue = a.total_movement || 0;
                    bValue = b.total_movement || 0;
            }

            if (sortOrder === 'asc') {
                return aValue - bValue;
            } else {
                return bValue - aValue;
            }
        });

        return filtered;
    }, [inventories, movementFilter, sortBy, sortOrder]);

    const exportToCSV = () => {
        const headers = ['Product', 'SKU', 'Total Stock', 'Movement', 'Transactions', 'Category', 'Ratio', 'Recommendation'];
        const csvData = filteredAndSortedInventories.map(inv => [
            inv.product?.name || '',
            inv.product?.sku || '',
            inv.total_quantity,
            inv.total_movement,
            inv.transaction_count,
            inv.movement_category.replace('_', ' ').toUpperCase(),
            inv.movement_ratio?.toFixed(2) || '0',
            inv.recommendation?.text || ''
        ]);

        const csvContent = [headers, ...csvData].map(row => 
            row.map(field => `"${field}"`).join(',')
        ).join('\n');

        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `movement-analysis-${period}-${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    const getMovementBadge = (category: string) => {
        const badges = {
            no_movement: 'bg-red-100 text-red-800',
            low_movement: 'bg-yellow-100 text-yellow-800', 
            medium_movement: 'bg-blue-100 text-blue-800',
            high_movement: 'bg-green-100 text-green-800'
        };
        return badges[category as keyof typeof badges] || 'bg-gray-100 text-gray-800';
    };

    const getRecommendationBadge = (status: string) => {
        const badges = {
            danger: 'bg-red-100 text-red-800',
            warning: 'bg-yellow-100 text-yellow-800',
            info: 'bg-blue-100 text-blue-800', 
            success: 'bg-green-100 text-green-800'
        };
        return badges[status as keyof typeof badges] || 'bg-gray-100 text-gray-800';
    };

    // Statistics calculation berdasarkan data yang sudah difilter
    const stats = useMemo(() => {
        const allInventories = inventories;
        const filtered = filteredAndSortedInventories;
        
        return {
            total: allInventories.length,
            filtered: filtered.length,
            noMovement: allInventories.filter(inv => inv.movement_category === 'no_movement').length,
            lowMovement: allInventories.filter(inv => inv.movement_category === 'low_movement').length,
            mediumMovement: allInventories.filter(inv => inv.movement_category === 'medium_movement').length,
            highMovement: allInventories.filter(inv => inv.movement_category === 'high_movement').length,
            avgRatio: allInventories.length > 0 ? (allInventories.reduce((sum, inv) => sum + (inv.movement_ratio || 0), 0) / allInventories.length).toFixed(2) : '0',
            needAttention: allInventories.filter(inv => ['no_movement', 'low_movement'].includes(inv.movement_category)).length,
            filteredAvgRatio: filtered.length > 0 ? (filtered.reduce((sum, inv) => sum + (inv.movement_ratio || 0), 0) / filtered.length).toFixed(2) : '0'
        };
    }, [inventories, filteredAndSortedInventories]);

    return (
        <AppLayout>
            <Head title="Inventory Movement Analysis" />
            <div className="px-4 py-6">
                <div className="flex justify-between items-center mb-6">
                    <div>
                        <h1 className="text-2xl font-semibold">Inventory Movement Analysis</h1>
                        <p className="text-gray-600 mt-1">
                            Stock movement analysis to optimize purchasing and prevent dead stock
                        </p>
                    </div>
                    <div className="flex gap-3">
                        <button 
                            onClick={exportToCSV}
                            className="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors"
                        >
                            Export CSV ({stats.filtered} items)
                        </button>
                        <Link href="/inventory" className="px-4 py-2 text-blue-600 hover:underline border border-blue-600 rounded-lg">
                            ← Back to Inventory
                        </Link>
                    </div>
                </div>

                {/* Controls */}
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                    {/* Period Filter */}
                    <div className="bg-white p-4 rounded-lg shadow">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Analysis Period:
                        </label>
                        <select 
                            value={period} 
                            onChange={(e) => handlePeriodChange(e.target.value)}
                            className="border border-gray-300 rounded-md px-3 py-2 bg-white w-full"
                        >
                            {Object.entries(periods).map(([key, label]) => (
                                <option key={key} value={key}>{label}</option>
                            ))}
                        </select>
                    </div>

                    {/* Movement Filter */}
                    <div className="bg-white p-4 rounded-lg shadow">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Movement Filter:
                        </label>
                        <div className="flex gap-1 flex-wrap">
                            <button 
                                onClick={() => setMovementFilter('all')}
                                className={`px-3 py-1 text-xs border rounded-full transition-colors ${
                                    movementFilter === 'all' 
                                        ? 'bg-blue-500 text-white border-blue-500' 
                                        : 'hover:bg-gray-50 border-gray-300'
                                }`}
                            >
                                All ({stats.total})
                            </button>
                            <button 
                                onClick={() => setMovementFilter('no_movement')}
                                className={`px-3 py-1 text-xs border rounded-full transition-colors ${
                                    movementFilter === 'no_movement' 
                                        ? 'bg-red-500 text-white border-red-500' 
                                        : 'hover:bg-red-50 text-red-600 border-red-200'
                                }`}
                            >
                                No Movement ({stats.noMovement})
                            </button>
                            <button 
                                onClick={() => setMovementFilter('low_movement')}
                                className={`px-3 py-1 text-xs border rounded-full transition-colors ${
                                    movementFilter === 'low_movement' 
                                        ? 'bg-yellow-500 text-white border-yellow-500' 
                                        : 'hover:bg-yellow-50 text-yellow-600 border-yellow-200'
                                }`}
                            >
                                Low ({stats.lowMovement})
                            </button>
                            <button 
                                onClick={() => setMovementFilter('medium_movement')}
                                className={`px-3 py-1 text-xs border rounded-full transition-colors ${
                                    movementFilter === 'medium_movement' 
                                        ? 'bg-blue-500 text-white border-blue-500' 
                                        : 'hover:bg-blue-50 text-blue-600 border-blue-200'
                                }`}
                            >
                                Medium ({stats.mediumMovement})
                            </button>
                            <button 
                                onClick={() => setMovementFilter('high_movement')}
                                className={`px-3 py-1 text-xs border rounded-full transition-colors ${
                                    movementFilter === 'high_movement' 
                                        ? 'bg-green-500 text-white border-green-500' 
                                        : 'hover:bg-green-50 text-green-600 border-green-200'
                                }`}
                            >
                                High ({stats.highMovement})
                            </button>
                        </div>
                    </div>

                    {/* Sort Options */}
                    <div className="bg-white p-4 rounded-lg shadow">
                        <label className="block text-sm font-medium text-gray-700 mb-2">
                            Sort by:
                        </label>
                        <div className="flex gap-2">
                            <select 
                                value={sortBy} 
                                onChange={(e) => setSortBy(e.target.value as 'movement' | 'quantity' | 'ratio')}
                                className="border border-gray-300 rounded-md px-3 py-2 bg-white flex-1"
                            >
                                <option value="movement">Movement</option>
                                <option value="quantity">Total Stock</option>
                                <option value="ratio">Ratio</option>
                            </select>
                            <button
                                onClick={() => setSortOrder(sortOrder === 'asc' ? 'desc' : 'asc')}
                                className="px-3 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
                                title={`Current: ${sortOrder === 'asc' ? 'Lowest to Highest' : 'Highest to Lowest'}`}
                            >
                                {sortOrder === 'asc' ? '↑' : '↓'}
                            </button>
                        </div>
                    </div>
                </div>

                {/* Summary Cards */}
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
                    <div className="bg-white p-4 rounded-lg shadow">
                        <h3 className="text-sm font-medium text-gray-600">Total Products</h3>
                        <p className="text-2xl font-bold text-gray-900">{stats.total}</p>
                        {movementFilter !== 'all' && (
                            <p className="text-xs text-gray-500">Showing: {stats.filtered}</p>
                        )}
                    </div>
                    <div className="bg-red-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-red-800">No Movement</h3>
                        <p className="text-2xl font-bold text-red-900">{stats.noMovement}</p>
                        <p className="text-xs text-red-600">
                            {stats.total > 0 ? ((stats.noMovement / stats.total) * 100).toFixed(1) : 0}%
                        </p>
                    </div>
                    <div className="bg-yellow-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-yellow-800">Low Movement</h3>
                        <p className="text-2xl font-bold text-yellow-900">{stats.lowMovement}</p>
                        <p className="text-xs text-yellow-600">
                            {stats.total > 0 ? ((stats.lowMovement / stats.total) * 100).toFixed(1) : 0}%
                        </p>
                    </div>
                    <div className="bg-blue-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-blue-800">Medium Movement</h3>
                        <p className="text-2xl font-bold text-blue-900">{stats.mediumMovement}</p>
                        <p className="text-xs text-blue-600">
                            {stats.total > 0 ? ((stats.mediumMovement / stats.total) * 100).toFixed(1) : 0}%
                        </p>
                    </div>
                    <div className="bg-green-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-green-800">High Movement</h3>
                        <p className="text-2xl font-bold text-green-900">{stats.highMovement}</p>
                        <p className="text-xs text-green-600">
                            {stats.total > 0 ? ((stats.highMovement / stats.total) * 100).toFixed(1) : 0}%
                        </p>
                    </div>
                    <div className="bg-purple-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-purple-800">Average Ratio</h3>
                        <p className="text-2xl font-bold text-purple-900">
                            {movementFilter === 'all' ? stats.avgRatio : stats.filteredAvgRatio}
                        </p>
                        <p className="text-xs text-purple-600">movement/stock</p>
                    </div>
                </div>

                {/* Key Insights */}
                {stats.needAttention > 0 && movementFilter === 'all' && (
                    <div className="bg-orange-50 border-l-4 border-orange-400 p-4 mb-6">
                        <div className="flex">
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-orange-800">
                                    Attention Required!
                                </h3>
                                <div className="mt-2 text-sm text-orange-700">
                                    <p>
                                        {stats.needAttention} products have low or no movement at all. 
                                        Consider reducing or stopping orders for these products in the upcoming period.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Filter Info */}
                {movementFilter !== 'all' && (
                    <div className="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                        <div className="flex">
                            <div className="ml-3">
                                <h3 className="text-sm font-medium text-blue-800">
                                    Active Filter: {movementFilter.replace('_', ' ').toUpperCase()}
                                </h3>
                                <div className="mt-2 text-sm text-blue-700">
                                    <p>
                                        Showing {stats.filtered} of {stats.total} products. 
                                        <button 
                                            onClick={() => setMovementFilter('all')}
                                            className="ml-2 underline hover:no-underline"
                                        >
                                            Show all
                                        </button>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                )}

                {/* Data Table */}
                <div className="bg-white rounded-lg shadow overflow-hidden">
                    <table className="min-w-full">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    onClick={() => setSortBy('quantity')}>
                                    Total Stock {sortBy === 'quantity' && (sortOrder === 'asc' ? '↑' : '↓')}
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    onClick={() => setSortBy('movement')}>
                                    Movement {sortBy === 'movement' && (sortOrder === 'asc' ? '↑' : '↓')}
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Category
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100"
                                    onClick={() => setSortBy('ratio')}>
                                    Ratio {sortBy === 'ratio' && (sortOrder === 'asc' ? '↑' : '↓')}
                                </th>
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Recommendation
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {filteredAndSortedInventories.map((inv, index) => (
                                <tr key={`${inv.product_id}-${inv.warehouse_id}`} className={index % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div className="text-sm font-medium text-gray-900">
                                                {inv.product?.name}
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                SKU: {inv.product?.sku}
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {inv.total_quantity?.toLocaleString()}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div className="text-sm font-medium text-gray-900">
                                                {inv.total_movement?.toLocaleString()} units
                                            </div>
                                            <div className="text-sm text-gray-500">
                                                {inv.transaction_count} transactions
                                            </div>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getMovementBadge(inv.movement_category)}`}>
                                            {inv.movement_category.replace('_', ' ').toUpperCase()}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {inv.movement_ratio?.toFixed(2)}
                                    </td>
                                    <td className="px-6 py-4">
                                        <span className={`inline-flex px-2 py-1 text-xs font-semibold rounded-full ${getRecommendationBadge(inv.recommendation?.status)}`}>
                                            {inv.recommendation?.status.toUpperCase()}
                                        </span>
                                        <div className="text-xs text-gray-600 mt-1">
                                            {inv.recommendation?.text}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {filteredAndSortedInventories.length === 0 && (
                    <div className="text-center py-8 text-gray-500">
                        {movementFilter === 'all' 
                            ? 'No inventory data for this period.'
                            : `No products with ${movementFilter.replace('_', ' ')} category.`
                        }
                    </div>
                )}
            </div>
        </AppLayout>
    );
}