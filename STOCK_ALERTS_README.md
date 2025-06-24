# 📦 Stock Alert System - Dokumentasi Lengkap

## 🚀 Fitur Stock Reminder/Alert System

Sistem notifikasi stok otomatis yang memberikan peringatan real-time ketika:
- **Stok Rendah**: Quantity ≤ Min Stock  
- **Stok Berlebih**: Quantity ≥ Max Stock

## ✅ Status Perbaikan
- **Role Error**: FIXED - Menggunakan role `admin` yang sudah ada
- **TypeScript Errors**: FIXED - Composable dengan ESLint compliant
- **Broadcasting**: Ready - Tinggal start Reverb server
- **Notifications**: WORKING - Tersimpan di database dan email
- **Customizable Recipients**: Notifikasi dikirim ke admin dan warehouse manager
- **Testing Tools**: API untuk testing notifikasi

## 📁 File yang Dibuat/Dimodifikasi

### Backend Files:
- `app/Notifications/StockAlertNotification.php` - Notifikasi stock alert
- `app/Events/StockLevelChanged.php` - Event perubahan level stok
- `app/Observers/InventoryObserver.php` - Observer untuk model Inventory
- `app/Models/Inventory.php` - Ditambah activity logging dan helper methods
- `app/Console/Commands/CheckStockLevels.php` - Command untuk cek stok berkala
- `app/Http/Controllers/StockTestController.php` - Controller untuk testing
- `app/Providers/AppServiceProvider.php` - Registrasi observers
- `routes/channels.php` - Broadcast channels
- `routes/web.php` - Testing routes

### Frontend Files:
- `resources/js/types/StockAlert.d.ts` - TypeScript interfaces
- `resources/js/composables/useStockAlerts.ts` - Vue composable untuk stock alerts

## ⚙️ Konfigurasi

### 1. Pastikan Broadcasting Sudah Dikonfigurasi
Pastikan file `.env` memiliki konfigurasi Reverb/Pusher:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### 2. Jalankan Migration (jika belum)
```bash
php artisan migrate
```

### 3. Jalankan Reverb Server
```bash
php artisan reverb:start
```

### 4. Compile Frontend Assets
```bash
npm run dev
```

## 📊 Cara Menggunakan

### Automatic Alerts
Sistem akan otomatis mengirim notifikasi ketika:
- Quantity inventory <= min_stock (Low Stock Alert)
- Quantity inventory >= max_stock (Overstock Alert)

### Manual Stock Check
```bash
# Cek semua stock levels
php artisan stock:check-levels

# Cek warehouse tertentu
php artisan stock:check-levels --warehouse-id=1

# Cek dan kirim notifikasi
php artisan stock:check-levels --send-notifications
```

### Testing API Endpoints
```bash
# Test low stock alert
POST /api/stock-test/low-stock

# Test overstock alert  
POST /api/stock-test/overstock

# Get current alerts
GET /api/stock-test/alerts

# Reset inventory to normal levels
POST /api/stock-test/reset
```

## 🎯 Frontend Integration

### Menggunakan Composable di Vue Component:
```javascript
<script setup>
import { useStockAlerts } from '@/composables/useStockAlerts';

const { state, markAsRead, clearAlerts } = useStockAlerts();

// state.alerts - Array of alerts
// state.unreadCount - Number of unread alerts
// state.isConnected - WebSocket connection status
</script>

<template>
  <div>
    <div v-if="state.unreadCount > 0" class="alert">
      {{ state.unreadCount }} new stock alerts
    </div>
    
    <div v-for="alert in state.alerts" :key="alert.inventory_id">
      <div :class="alert.type === 'low_stock' ? 'text-red-500' : 'text-orange-500'">
        {{ alert.message }}
      </div>
    </div>
  </div>
</template>
```

## 🔧 Kustomisasi

### Mengubah Recipients
Edit method `getStockAlertRecipients()` di `InventoryObserver.php`:

```php
private function getStockAlertRecipients(Inventory $inventory)
{
    // Contoh: Tambah user berdasarkan warehouse
    return User::role(['admin', 'warehouse_manager'])
               ->orWhere('warehouse_id', $inventory->warehouse_id)
               ->get();
}
```

### Mengubah Alert Conditions
Edit method `determineAlertType()` di `InventoryObserver.php`:

```php
private function determineAlertType(Inventory $inventory): ?string
{
    // Custom logic untuk alert conditions
    if ($inventory->quantity <= ($inventory->min_stock * 0.8)) {
        return 'critical_low_stock';
    }
    // ... dst
}
```

## 📱 Browser Notifications

System otomatis meminta permission dan menampilkan browser notifications. User akan melihat:
- 🔻 **Stok Rendah** - untuk low stock alerts
- 🔺 **Stok Berlebih** - untuk overstock alerts

## 🗂️ Database Structure

Notifications disimpan di tabel `notifications` dengan structure:
- `type` - jenis notifikasi (App\Notifications\StockAlertNotification)
- `data` - JSON data dengan informasi lengkap alert
- `read_at` - timestamp saat notifikasi dibaca

## 🚦 Channel Authorization

Broadcast channels menggunakan authorization:
- `stock-alerts` - Hanya admin dan warehouse_manager
- `warehouse.{id}` - User dengan akses ke warehouse tertentu

## 🔍 Debugging

### Log Monitoring
```bash
tail -f storage/logs/laravel.log | grep -i "stock"
```

### Test WebSocket Connection
```javascript
// Di browser console
Echo.private('stock-alerts')
  .listen('stock.alert', (data) => {
    console.log('Stock alert received:', data);
  });
```

## 📈 Monitoring & Analytics

Sistem mencatat semua activity dengan Spatie Activity Log:
- Perubahan inventory quantity
- Notifikasi yang dikirim
- Error handling

## 🔄 Scheduled Tasks (Optional)

Tambahkan ke `app/Console/Kernel.php` untuk cek berkala:

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('stock:check-levels --send-notifications')
             ->hourly()
             ->withoutOverlapping();
}
```

## 🎨 UI Components (Recommended)

Buat components untuk:
- Stock Alert Badge/Counter
- Stock Alert List/Dropdown  
- Stock Level Indicators
- Real-time connection status

---

**Enjoy your real-time stock monitoring! 🎉**
