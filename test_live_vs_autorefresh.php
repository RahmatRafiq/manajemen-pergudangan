<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Events\StockLevelChanged;
use App\Models\Inventory;

echo "=== 🔍 TEST PURE LIVE UPDATE (NO AUTO-REFRESH) ===\n\n";

$inventory = Inventory::first();

echo "� AUTO-REFRESH SUDAH DI-DISABLE\n";
echo "🎯 SEKARANG HANYA TEST LIVE UPDATE (WebSocket)\n\n";

echo "📱 INSTRUKSI:\n";
echo "1. Buka browser: http://localhost:5173/stock-alerts\n";
echo "2. Buka F12 → Console\n";
echo "3. Clear console (Ctrl+L)\n";
echo "4. Refresh halaman jika perlu\n";
echo "5. Pastikan TIDAK ada log auto-refresh\n\n";

echo "🚀 Broadcasting LIVE UPDATE event...\n";

// Broadcast event langsung
$event = new StockLevelChanged(
    inventory: $inventory,
    oldQuantity: 50,
    newQuantity: 2, // Low stock
    alertType: 'low_stock'
);

broadcast($event)->toOthers();

echo "✅ Event telah di-broadcast ke WebSocket!\n\n";

echo "📊 EXPECTED HASIL JIKA LIVE UPDATE BEKERJA:\n";
echo "   ⚡ INSTANT (< 1 detik):\n";
echo "   - Console: '📊 *** LIVE UPDATE EVENT RECEIVED (PUBLIC CHANNEL) ***'\n";
echo "   - Console: '📊 stock.level.changed event received'\n";
echo "   - Console: '🚨 addAlert called with:'\n";
echo "   - Console: '🍞 Attempting to show Toastify'\n";
echo "   - Console: '✅ Toastify notification shown successfully'\n";
echo "   - Toastify popup muncul\n";
echo "   - Alert baru di halaman\n\n";

echo "❌ JIKA LIVE UPDATE GAGAL:\n";
echo "   - Tidak ada log live update sama sekali\n";
echo "   - Tidak ada Toastify\n";
echo "   - Tidak ada alert baru di halaman\n";
echo "   - HARUS refresh manual untuk lihat alert\n\n";

echo "⏱️  Tunggu 5 detik dan report hasilnya...\n";
echo "💡 Hasil: LIVE UPDATE bekerja atau gagal?\n";
