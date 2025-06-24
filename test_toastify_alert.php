<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inventory;

echo "=== TOASTIFY STOCK ALERT TEST ===\n";
echo "Memicu alert untuk test notifikasi Toastify\n\n";

// Ambil inventory pertama
$inventory = Inventory::first();

if (!$inventory) {
    echo "❌ Tidak ada inventory ditemukan!\n";
    exit(1);
}

echo "📦 Produk: {$inventory->product->name}\n";
echo "🏪 Gudang: {$inventory->warehouse->name}\n";
echo "📊 Stok saat ini: {$inventory->quantity}\n";
echo "🔻 Min stock: {$inventory->min_stock}\n";
echo "🔺 Max stock: {$inventory->max_stock}\n\n";

// Set ke jumlah yang akan memicu low stock alert
$newQuantity = 1;
echo "🚨 Mengubah stok menjadi {$newQuantity} untuk memicu LOW STOCK ALERT...\n";

$inventory->update(['quantity' => $newQuantity]);

echo "✅ Stok berhasil diubah!\n";
echo "📱 Cek browser Anda - notifikasi Toastify harus muncul!\n";
echo "🔗 Buka: http://localhost:5173/stock-alerts\n\n";

echo "💡 Tips debugging:\n";
echo "1. Buka F12 → Console di browser\n";
echo "2. Lihat log: '📊 StockLevelChanged event received'\n";
echo "3. Lihat log: '🍞 Attempting to show Toastify'\n";
echo "4. Lihat log: '✅ Toastify notification shown successfully'\n\n";

echo "🔄 Untuk test lagi, jalankan: php test_toastify_alert.php\n";
