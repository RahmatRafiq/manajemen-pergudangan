<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inventory;

echo "=== 🔄 LIVE UPDATE TEST UNTUK STOCK ALERTS ===\n";
echo "Test ini akan memicu perubahan inventory dan cek live update\n\n";

// Ambil inventory pertama dengan min_stock yang jelas
$inventory = Inventory::whereNotNull('min_stock')
    ->where('min_stock', '>', 0)
    ->first();

if (!$inventory) {
    echo "❌ Tidak ada inventory dengan min_stock ditemukan!\n";
    exit(1);
}

echo "📦 Produk: {$inventory->product->name}\n";
echo "🏪 Gudang: {$inventory->warehouse->name}\n";
echo "📊 Stok saat ini: {$inventory->quantity}\n";
echo "🔻 Min stock: {$inventory->min_stock}\n";
echo "🔺 Max stock: {$inventory->max_stock}\n\n";

// Step 1: Set normal quantity (tidak alert)
$normalQty = $inventory->min_stock + 5;
echo "🔄 STEP 1: Setting normal quantity ({$normalQty}) - tidak ada alert\n";
$inventory->update(['quantity' => $normalQty]);
echo "   ✅ Stok diubah ke: {$inventory->fresh()->quantity}\n";
echo "   📱 Cek browser: TIDAK ada notifikasi (normal stock)\n\n";

sleep(2);

// Step 2: Set low stock - harus trigger alert
$lowQty = $inventory->min_stock - 5;
echo "🚨 STEP 2: Setting LOW STOCK ({$lowQty}) - harus trigger alert\n";
$inventory->update(['quantity' => $lowQty]);
echo "   ✅ Stok diubah ke: {$inventory->fresh()->quantity}\n";
echo "   📱 Cek browser: HARUS ada notifikasi LOW STOCK\n";
echo "   🔗 Buka: http://localhost:5173/stock-alerts\n\n";

sleep(2);

// Step 3: Set overstock - harus trigger alert
if ($inventory->max_stock) {
    $overQty = $inventory->max_stock + 10;
    echo "🔺 STEP 3: Setting OVERSTOCK ({$overQty}) - harus trigger alert\n";
    $inventory->update(['quantity' => $overQty]);
    echo "   ✅ Stok diubah ke: {$inventory->fresh()->quantity}\n";
    echo "   📱 Cek browser: HARUS ada notifikasi OVERSTOCK\n\n";
} else {
    echo "⚠️  STEP 3: Skipped (no max_stock defined)\n\n";
}

echo "🔍 DEBUGGING CHECKLIST:\n";
echo "1. ✅ Buka F12 → Console di browser\n";
echo "2. 🔍 Cari log: '📊 stock.level.changed event received'\n";
echo "3. 🔍 Cari log: '🍞 Attempting to show Toastify'\n";
echo "4. 🔍 Cari log: '✅ Toastify notification shown successfully'\n";
echo "5. 👁️  Lihat apakah alert baru muncul di halaman TANPA refresh\n";
echo "6. 🔔 Lihat apakah Toastify popup muncul\n";
echo "7. 🔢 Lihat apakah counter unread bertambah\n\n";

echo "💡 EXPECTED BEHAVIOR:\n";
echo "- Step 1: Tidak ada alert (quantity normal)\n";
echo "- Step 2: LOW STOCK alert muncul + Toastify + live update\n";
echo "- Step 3: OVERSTOCK alert muncul + Toastify + live update\n\n";

echo "🔄 Untuk test lagi: php test_live_update.php\n";
