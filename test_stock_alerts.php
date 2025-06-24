<?php

// Test script to verify stock alert system functionality
// Run this in the Laravel project root: php test_stock_alerts.php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Illuminate\Foundation\Bootstrap\RegisterFacades;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Bootstrap\BootProviders;

$app = new Application(__DIR__);

// Bootstrap the application
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

(new LoadEnvironmentVariables)->bootstrap($app);
(new LoadConfiguration)->bootstrap($app);
(new HandleExceptions)->bootstrap($app);
(new RegisterFacades)->bootstrap($app);
(new RegisterProviders)->bootstrap($app);
(new BootProviders)->bootstrap($app);

echo "🧪 Testing Stock Alert System\n";
echo "==============================\n\n";

// Test 1: Check if admin user exists
echo "1. Checking for admin user...\n";
$adminUser = App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'admin');
})->first();

if (!$adminUser) {
    echo "❌ No admin user found. Please create an admin user first.\n";
    echo "   Run: php artisan db:seed --class=DatabaseSeeder\n\n";
    exit(1);
}

echo "✅ Admin user found: {$adminUser->name} ({$adminUser->email})\n\n";

// Test 2: Check if there are inventories with stock levels
echo "2. Checking inventory data...\n";
$inventories = App\Models\Inventory::with(['product', 'warehouse'])->take(5)->get();

if ($inventories->isEmpty()) {
    echo "❌ No inventory data found. Please seed some inventory data first.\n\n";
    exit(1);
}

echo "✅ Found {$inventories->count()} inventory items:\n";
foreach ($inventories as $inventory) {
    echo "   - {$inventory->product->name} at {$inventory->warehouse->name}: {$inventory->quantity} units\n";
    echo "     Min: {$inventory->min_stock}, Max: {$inventory->max_stock}\n";
}
echo "\n";

// Test 3: Send a test low stock alert
echo "3. Sending test low stock alert...\n";
$testInventory = $inventories->first();

if ($testInventory->min_stock && $testInventory->quantity > $testInventory->min_stock) {
    // Temporarily reduce stock to trigger low stock alert
    $originalQuantity = $testInventory->quantity;
    $testInventory->quantity = $testInventory->min_stock - 1;
    $testInventory->save();
    
    echo "✅ Triggered low stock alert for {$testInventory->product->name}\n";
    echo "   Quantity changed from {$originalQuantity} to {$testInventory->quantity}\n";
    
    // Restore original quantity
    $testInventory->quantity = $originalQuantity;
    $testInventory->save();
    echo "   Quantity restored to {$originalQuantity}\n\n";
} else {
    echo "⚠️  Cannot trigger low stock alert - no suitable inventory found\n\n";
}

// Test 4: Check if notifications were created
echo "4. Checking notifications in database...\n";
$notifications = DB::table('notifications')
    ->where('type', 'App\\Notifications\\StockAlertNotification')
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->get();

if ($notifications->isEmpty()) {
    echo "❌ No stock alert notifications found in database\n";
} else {
    echo "✅ Found {$notifications->count()} stock alert notifications:\n";
    foreach ($notifications as $notification) {
        $data = json_decode($notification->data, true);
        echo "   - {$data['message']} (created: {$notification->created_at})\n";
    }
}
echo "\n";

// Test 5: Run the stock check command
echo "5. Running stock check command...\n";
try {
    $exitCode = Artisan::call('stock:check');
    if ($exitCode === 0) {
        echo "✅ Stock check command completed successfully\n";
        echo Artisan::output();
    } else {
        echo "❌ Stock check command failed with exit code: {$exitCode}\n";
    }
} catch (Exception $e) {
    echo "❌ Error running stock check command: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Check queue worker status
echo "6. Queue worker information...\n";
echo "   Make sure to run: php artisan queue:work\n";
echo "   And also run: php artisan reverb:start\n\n";

echo "🎉 Stock alert system test completed!\n";
echo "=====================================\n\n";

echo "Next steps:\n";
echo "1. Start queue worker: php artisan queue:work\n";
echo "2. Start Reverb server: php artisan reverb:start\n";
echo "3. Visit /stock-alerts in your browser\n";
echo "4. Modify inventory quantities to trigger real-time alerts\n\n";
