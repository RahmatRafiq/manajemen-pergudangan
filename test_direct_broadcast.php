<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\StockLevelChanged;
use App\Models\Inventory;

echo "=== 🚀 DIRECT BROADCAST TEST ===\n";
echo "Test langsung broadcast tanpa queue\n\n";

$inventory = Inventory::first();

echo "📦 Produk: {$inventory->product->name}\n";
echo "🏪 Gudang: {$inventory->warehouse->name}\n";
echo "📊 Stok saat ini: {$inventory->quantity}\n\n";

echo "🚀 Broadcasting event langsung...\n";

// Broadcast langsung tanpa queue
$event = new StockLevelChanged(
    inventory: $inventory,
    oldQuantity: 99,
    newQuantity: 1, // Low stock
    alertType: 'low_stock'
);

// Broadcast now - tidak pakai queue
broadcast($event)->toOthers();

echo "✅ Event telah di-broadcast!\n";
echo "📱 Cek browser SEKARANG - harus ada log:\n";
echo "   📊 *** LIVE UPDATE EVENT RECEIVED (PUBLIC CHANNEL) ***\n";
echo "   📊 stock.level.changed event received\n";
echo "   🍞 Toastify notification harus muncul\n\n";

echo "🔗 URL: http://localhost:5173/stock-alerts\n";
echo "💡 Jika tidak ada log, berarti Echo connection bermasalah\n";
