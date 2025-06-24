<?php

use App\Events\StockLevelChanged;
use App\Models\Inventory;

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 🎯 FINAL LIVE UPDATE TEST ===\n";
echo "🔍 Testing PURE WebSocket Live Update (No Auto-refresh)\n\n";

// Test 1: Check components status
echo "📋 STEP 1: Checking system components...\n";

// Check if Reverb is running
$reverbRunning = false;
$output = shell_exec('netstat -an | findstr :8080');
if ($output && strpos($output, 'LISTENING') !== false) {
    echo "✅ Reverb server: RUNNING on port 8080\n";
    $reverbRunning = true;
} else {
    echo "❌ Reverb server: NOT RUNNING\n";
}

// Check if Vite is running
$viteRunning = false;
$output = shell_exec('netstat -an | findstr :5173');
if ($output && strpos($output, 'LISTENING') !== false) {
    echo "✅ Vite dev server: RUNNING on port 5173\n";
    $viteRunning = true;
} else {
    echo "❌ Vite dev server: NOT RUNNING\n";
}

// Check broadcasting config
$broadcastDriver = config('broadcasting.default');
echo "📡 Broadcasting driver: {$broadcastDriver}\n";

if (!$reverbRunning || !$viteRunning) {
    echo "\n❌ CRITICAL: Required services not running!\n";
    echo "🔧 Please start missing services:\n";
    if (!$reverbRunning) echo "   - php artisan reverb:start\n";
    if (!$viteRunning) echo "   - npm run dev\n";
    exit(1);
}

echo "\n📋 STEP 2: Getting test inventory...\n";

// Get existing inventory
$inventory = Inventory::with(['product', 'warehouse'])->first();

if (!$inventory) {
    echo "❌ No inventory found in database!\n";
    exit(1);
}

echo "🎯 Test inventory:\n";
echo "   Product: {$inventory->product->name}\n";
echo "   Warehouse: {$inventory->warehouse->name}\n";
echo "   Current Stock: {$inventory->quantity}\n";
echo "   Min Stock: {$inventory->min_stock}\n";

echo "\n📋 STEP 3: Broadcasting event...\n";

try {
    // Create and broadcast event with low stock alert
    $oldQuantity = $inventory->quantity;
    $newQuantity = 2; // Force low stock
    $alertType = 'low_stock';
    
    $event = new StockLevelChanged(
        $inventory,
        $oldQuantity,
        $newQuantity,
        $alertType
    );

    // Broadcast sync (immediate)
    broadcast($event)->toOthers();
    
    echo "✅ Event broadcast SUCCESSFUL!\n";
    
} catch (Exception $e) {
    echo "❌ Event broadcast FAILED: {$e->getMessage()}\n";
    exit(1);
}

echo "\n📋 STEP 4: Testing instructions...\n";
echo "🌐 Open browser: http://localhost:5173/stock-alerts\n";
echo "🛠️  Open DevTools (F12) → Console tab\n";
echo "🔄 Refresh page if needed\n";
echo "👀 Watch console for these logs:\n\n";

echo "✅ EXPECTED SUCCESS LOGS (should appear within 1-2 seconds):\n";
echo "   🟢 Echo: Successfully connected to Reverb\n";
echo "   📊 *** LIVE UPDATE EVENT RECEIVED (PUBLIC CHANNEL) ***\n";
echo "   📊 stock.level.changed event received: {...}\n";
echo "   🎯 Alert type detected: low_stock\n";
echo "   ✅ Creating alert from stock level change\n";
echo "   📤 Calling addAlert with: {...}\n";
echo "   🚨 addAlert called with: {...}\n";
echo "   🍞 Attempting to show Toastify: {...}\n";
echo "   ✅ Toastify notification shown successfully\n";
echo "   + Toastify popup appears in top-right\n";
echo "   + New alert appears in the alerts list\n\n";

echo "❌ FAILURE INDICATORS:\n";
echo "   🔴 Echo: Disconnected from Reverb\n";
echo "   ❌ Echo: Connection error: {...}\n";
echo "   ⚠️  Echo not available, retrying...\n";
echo "   - No live update logs appear\n";
echo "   - No Toastify notification\n";
echo "   - Page needs manual refresh to see alert\n\n";

echo "🚫 AUTO-REFRESH STATUS: DISABLED ✓\n";
echo "🔍 This test ONLY checks WebSocket live update\n";
echo "⏱️  Give it 10 seconds, then report your results!\n\n";

echo "💡 WHAT TO REPORT:\n";
echo "   ✅ SUCCESS: 'Live update works! Toastify appeared instantly'\n";
echo "   ❌ FAILURE: 'No logs, no Toastify, needs manual refresh'\n";
echo "   🟡 PARTIAL: 'Connected but no event received'\n\n";

echo "🎯 Event broadcast complete. Check browser NOW!\n";
