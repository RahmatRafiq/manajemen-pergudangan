<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\StockTestController;
use App\Http\Controllers\StockAlertController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::get('auth/{provider}', [SocialAuthController::class, 'redirectToProvider'])->name('auth.redirect');
Route::get('auth/{provider}/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('auth.callback');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // API Routes for Stock Alerts
    Route::prefix('api')->group(function () {
        Route::get('/stock-alerts', [StockAlertController::class, 'getAlertsApi'])->name('api.stock-alerts');
    });

    // Stock Alerts Routes
    Route::get('/stock-alerts', [StockAlertController::class, 'index'])->name('stock-alerts.index');
    Route::patch('/stock-alerts/{alertId}/read', [StockAlertController::class, 'markAsRead'])->name('stock-alerts.read');
    Route::patch('/stock-alerts/read-all', [StockAlertController::class, 'markAllAsRead'])->name('stock-alerts.read-all');
    Route::delete('/stock-alerts/clear', [StockAlertController::class, 'clear'])->name('stock-alerts.clear');

    // Common file and storage routes (accessible by all authenticated users)
    Route::delete('/settings/profile/delete-file', [\App\Http\Controllers\Settings\ProfileController::class, 'deleteFile'])->name('profile.deleteFile');
    Route::post('/settings/profile/upload', [\App\Http\Controllers\Settings\ProfileController::class, 'upload'])->name('profile.upload');
    Route::post('/temp/storage', [\App\Http\Controllers\StorageController::class, 'store'])->name('storage.store');
    Route::delete('/temp/storage', [\App\Http\Controllers\StorageController::class, 'destroy'])->name('storage.destroy');
    Route::get('/temp/storage/{path}', [\App\Http\Controllers\StorageController::class, 'show'])->name('storage.show');

    // Inventory view routes (accessible by all authenticated users)
    Route::get('/inventory/sorted/warehouse/{warehouseId}', [\App\Http\Controllers\InventorySortController::class, 'byWarehouse']);
    Route::get('/inventory/sorted/global', [\App\Http\Controllers\InventorySortController::class, 'global']);
    Route::get('/inventory/sorted/warehouse/{warehouseId}/json', [\App\Http\Controllers\InventorySortController::class, 'jsonByWarehouse']);
    Route::get('/inventory/sorted/global/json', [\App\Http\Controllers\InventorySortController::class, 'jsonGlobal']);
    Route::get('/inventory/movement/statistics', [\App\Http\Controllers\InventorySortController::class, 'getMovementStatistics']);

    Route::post('logout', [SocialAuthController::class, 'logout'])->name('logout');

    // Admin Only Routes
    Route::middleware(['role:admin'])->group(function () {
        // User, Role & Permission Management (Admin Only)
        Route::post('roles/json', [\App\Http\Controllers\UserRolePermission\RoleController::class, 'json'])->name('roles.json');
        Route::resource('roles', \App\Http\Controllers\UserRolePermission\RoleController::class);

        Route::post('permissions/json', [\App\Http\Controllers\UserRolePermission\PermissionController::class, 'json'])->name('permissions.json');
        Route::resource('permissions', \App\Http\Controllers\UserRolePermission\PermissionController::class);

        Route::post('users/json', [\App\Http\Controllers\UserRolePermission\UserController::class, 'json'])->name('users.json');
        Route::resource('users', \App\Http\Controllers\UserRolePermission\UserController::class);
        Route::get('users/trashed', [\App\Http\Controllers\UserRolePermission\UserController::class, 'trashed'])->name('users.trashed');
        Route::post('users/{user}/restore', [\App\Http\Controllers\UserRolePermission\UserController::class, 'restore'])->name('users.restore');
        Route::delete('users/{user}/force-delete', [\App\Http\Controllers\UserRolePermission\UserController::class, 'forceDelete'])->name('users.force-delete');

        // Stock Test Routes (Admin Only for security)
        Route::prefix('stock-test')->group(function () {
            Route::post('/low-stock', [StockAlertController::class, 'testLowStock'])->name('stock-test.low-stock');
            Route::post('/overstock', [StockAlertController::class, 'testOverstock'])->name('stock-test.overstock');
            Route::get('/alerts', [StockAlertController::class, 'getStockAlerts'])->name('stock-test.alerts');
            Route::post('/reset', [StockAlertController::class, 'resetInventory'])->name('stock-test.reset');
        });
    });

    // Admin Only Routes - Category & Product Management
    Route::middleware(['role:admin'])->group(function () {
        // Category Management (Admin Only)
        Route::post('category/json', [\App\Http\Controllers\CategoryController::class, 'json'])->name('category.json');
        Route::resource('category', \App\Http\Controllers\CategoryController::class);
        Route::get('category/trashed', [\App\Http\Controllers\CategoryController::class, 'trashed'])->name('category.trashed');
        Route::post('category/{category}/restore', [\App\Http\Controllers\CategoryController::class, 'restore'])->name('category.restore');
        Route::delete('category/{category}/force-delete', [\App\Http\Controllers\CategoryController::class, 'forceDelete'])->name('category.force-delete');

        // Product Management (Admin Only)
        Route::post('product/json', [\App\Http\Controllers\ProductController::class, 'json'])->name('product.json');
        Route::resource('product', \App\Http\Controllers\ProductController::class);
        Route::get('product/trashed', [\App\Http\Controllers\ProductController::class, 'trashed'])->name('product.trashed');
        Route::post('product/{product}/restore', [\App\Http\Controllers\ProductController::class, 'restore'])->name('product.restore');
        Route::delete('product/{product}/force-delete', [\App\Http\Controllers\ProductController::class, 'forceDelete'])->name('product.force-delete');
    });

    // Inventory & Transactions (All authenticated users can access)
    Route::group([], function () {
        // Warehouse Management (All users)
        Route::post('warehouse/json', [\App\Http\Controllers\WarehouseController::class, 'json'])->name('warehouse.json');
        Route::resource('warehouse', \App\Http\Controllers\WarehouseController::class);
        Route::get('warehouse/trashed', [\App\Http\Controllers\WarehouseController::class, 'trashed'])->name('warehouse.trashed');
        Route::post('warehouse/{warehouse}/restore', [\App\Http\Controllers\WarehouseController::class, 'restore'])->name('warehouse.restore');
        Route::delete('warehouse/{warehouse}/force-delete', [\App\Http\Controllers\WarehouseController::class, 'forceDelete'])->name('warehouse.force-delete');

        // Inventory Management (All users)
        Route::post('inventory/json', [\App\Http\Controllers\InventoryController::class, 'json'])->name('inventory.json');
        Route::resource('inventory', \App\Http\Controllers\InventoryController::class);
        Route::get('inventory/trashed', [\App\Http\Controllers\InventoryController::class, 'trashed'])->name('inventory.trashed');
        Route::post('inventory/{inventory}/restore', [\App\Http\Controllers\InventoryController::class, 'restore'])->name('inventory.restore');
        Route::delete('inventory/{inventory}/force-delete', [\App\Http\Controllers\InventoryController::class, 'forceDelete'])->name('inventory.force-delete');

        // Stock Transaction Management (All users)
        Route::post('stock-transaction/json', [\App\Http\Controllers\StockTransactionController::class, 'json'])->name('stock-transaction.json');
        Route::resource('stock-transaction', \App\Http\Controllers\StockTransactionController::class);
        Route::get('stock-transaction/trashed', [\App\Http\Controllers\StockTransactionController::class, 'trashed'])->name('stock-transaction.trashed');
        Route::post('stock-transaction/{transaction}/restore', [\App\Http\Controllers\StockTransactionController::class, 'restore'])->name('stock-transaction.restore');
        Route::delete('stock-transaction/{transaction}/force-delete', [\App\Http\Controllers\StockTransactionController::class, 'forceDelete'])->name('stock-transaction.force-delete');
    });

});
// Activity Log Routes (Admin Only)
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard/activity-logs', function () {
        return Inertia::render('ActivityLogList');
    })->name('dashboard.activity-logs');
    
    Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-log.index');
});

// Test route untuk debugging middleware protection
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/test-admin-access', function () {
        return Inertia::render('Dashboard');
    })->name('test.admin');
});

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/test-user-access', function () {
        return Inertia::render('Dashboard');
    })->name('test.user');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
