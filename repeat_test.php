<?php

use App\Events\StockLevelChanged;
use App\Models\Inventory;

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== 🔄 REPEAT LIVE UPDATE TEST ===\n";
echo "Script untuk test live update berulang\n\n";

// Get random inventory
$inventory = Inventory::with(['product', 'warehouse'])->inRandomOrder()->first();

if (!$inventory) {
    echo "❌ No inventory found!\n";
    exit(1);
}

$testNumber = rand(1, 999);
echo "🎯 Test #{$testNumber}\n";
echo "📦 Product: {$inventory->product->name}\n";
echo "🏪 Warehouse: {$inventory->warehouse->name}\n";

// Alternate between low_stock and overstock
$alertType = rand(0, 1) ? 'low_stock' : 'overstock';
$oldQuantity = $inventory->quantity;
$newQuantity = $alertType === 'low_stock' ? 1 : 999;

echo "⚠️  Alert: {$alertType}\n";
echo "📊 Stock: {$oldQuantity} → {$newQuantity}\n";

echo "\n🚀 Broadcasting event...\n";

try {
    $event = new StockLevelChanged($inventory, $oldQuantity, $newQuantity, $alertType);
    broadcast($event)->toOthers();
    
    echo "✅ Event broadcast SUCCESSFUL!\n";
    echo "👀 Check browser console for logs!\n";
    echo "🍞 Toastify should appear for: {$inventory->product->name}\n";
    
} catch (Exception $e) {
    echo "❌ Broadcast failed: {$e->getMessage()}\n";
}
