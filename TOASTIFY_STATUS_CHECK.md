# 🧪 PANDUAN UJI NOTIFIKASI TOASTIFY - UPDATE

## ✅ MASALAH YANG SUDAH DIPERBAIKI

🔧 **Event Listener Fixed**: Hook sekarang listen ke event `stock.level.changed` (bukan `StockLevelChanged`)  
🔧 **Toastify Positioning**: Ditambahkan `close: true`, `gravity: "top"`, `position: "right"`  
🔧 **Enhanced Logging**: Log debugging yang lebih detail untuk troubleshooting  
🔧 **CSS Styling**: Custom Toastify styles ditambahkan ke `app.css`

## 🚀 CARA TEST NOTIFIKASI TOASTIFY

### 1. Test Manual (Tombol Test Toast) ✅
1. Buka browser ke: `http://localhost:5173/stock-alerts`
2. Klik tombol **"🧪 Test Toast"** di kanan atas  
3. ✅ Harus muncul notifikasi hijau: "🧪 Test Toast Notification - Toastify bekerja!"

### 2. Test Real-time Alert (Event-Driven) 🔄

#### Langkah 1: Pastikan Layanan Berjalan
```powershell
# Cara mudah: Jalankan batch script
start-stock-alerts.bat

# Atau manual di 3 terminal terpisah:
# Terminal 1:
php artisan queue:work

# Terminal 2: 
php artisan reverb:start

# Terminal 3:
npm run dev
```

#### Langkah 2: Buka Browser & Console
1. Buka `http://localhost:5173/stock-alerts`
2. Tekan **F12** → buka tab **Console**
3. Pastikan melihat log: `🔗 Setting up Echo listeners...`
4. Pastikan melihat log: `✅ Echo listeners setup complete`

#### Langkah 3: Trigger Alert
```powershell
# Jalankan script test
php test_toastify_alert.php
```

#### Langkah 4: Cek Console Log
Harus muncul urutan log berikut di console browser:
```
📊 stock.level.changed event received: {data...}
🎯 Alert type detected: low_stock
✅ Creating alert from stock level change
📤 Calling addAlert with: {alert...}
🚨 addAlert called with: {alert...}
🍞 Attempting to show Toastify: {title, message}
✅ Toastify notification shown successfully
```

#### Langkah 5: Lihat Notifikasi
- 🟨 **Toastify popup** harus muncul di kanan atas
- 🔔 **Browser notification** (jika permission granted)
- 📱 **Alert baru** di list halaman

## 🔍 TROUBLESHOOTING

### Toastify Tidak Muncul Sama Sekali?

1. **Cek tombol Test Toast dulu**:
   - Jika Test Toast tidak bekerja → masalah Toastify library
   - Jika Test Toast bekerja → masalah event listener/Echo

2. **Cek console errors**:
   ```javascript
   // Buka F12 → Console, cari error merah
   ```

3. **Cek CSS Toastify**:
   - F12 → Network tab → refresh halaman
   - Cari `toastify.css` - harus loaded 200 OK

### Real-time Alert Tidak Muncul?

1. **Cek koneksi Echo**:
   ```javascript
   // Di console browser:
   console.log('Echo:', window.Echo);
   console.log('Connected:', window.Echo?.connector?.pusher?.connection?.state);
   ```

2. **Cek apakah event diterima**:
   - Harus ada log `� stock.level.changed event received`
   - Jika tidak ada → masalah broadcasting/connection

3. **Cek layanan berjalan**:
   ```powershell
   # Cek proses PHP dan Node
   Get-Process | Where-Object {$_.ProcessName -like "*php*" -or $_.ProcessName -like "*node*"}
   ```

### Queue Jobs Gagal?

```powershell
# Cek failed jobs
php artisan queue:failed

# Retry all failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

## 📝 EXPECTED BEHAVIOR

### Saat Stock Berubah:
1. ⚡ Observer `InventoryObserver` detect perubahan
2. 🚀 Event `StockLevelChanged` di-fire & queued
3. 📡 Broadcasting job diproses oleh queue worker  
4. 📻 Reverb server broadcast ke clients
5. 🎧 React hook receive event `stock.level.changed`
6. 🍞 Toastify notification muncul di browser
7. 📊 Alert ditambahkan ke state & database

### Test Berhasil Jika:
- ✅ Test Toast button bekerja
- ✅ Console log menunjukkan event received
- ✅ Toastify popup muncul untuk real alert
- ✅ Alert muncul di halaman tanpa refresh
- ✅ Indicator connection status hijau

## 🔄 RESET & CLEAN TEST

Jika masih bermasalah, lakukan reset:

```powershell
# 1. Stop semua proses
taskkill /f /im php.exe
taskkill /f /im node.exe

# 2. Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 3. Restart layanan
start-stock-alerts.bat

# 4. Test lagi
php test_toastify_alert.php
```

**Status: Hook sudah diperbaiki dengan event listener yang benar! 🎉**
