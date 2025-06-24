# 🔄 TROUBLESHOOTING LIVE UPDATE STOCK ALERTS

## ✅ Test yang Sudah Dijalankan
- Backend: inventory berhasil diubah (normal → low stock → overstock)
- Observer: harus detect perubahan dan fire event
- Broadcasting: event harus di-broadcast ke channel
- Frontend: Echo harus receive event dan trigger Toastify + live update

## 🔍 DEBUGGING STEP BY STEP

### Step 1: Buka Browser & Console
```
1. Buka http://localhost:5173/stock-alerts
2. Tekan F12 → buka tab Console
3. Clear console (Ctrl+L)
4. Refresh halaman jika perlu
```

### Step 2: Cek Initial Setup
Console harus menampilkan:
```
🔗 Setting up Echo listeners...
✅ Echo listeners setup complete
🟢 Connected to stock alerts channel
```

Jika TIDAK muncul:
- ❌ Echo tidak ter-setup atau Reverb server tidak berjalan
- 🔧 Jalankan: `php artisan reverb:start` di terminal terpisah

### Step 3: Test Event Reception
Jalankan test lagi:
```powershell
php test_live_update.php
```

Console browser harus menampilkan (untuk setiap perubahan):
```
📊 stock.level.changed event received: {data...}
🎯 Alert type detected: low_stock (atau overstock)
✅ Creating alert from stock level change
📤 Calling addAlert with: {alert...}
🚨 addAlert called with: {alert...}
🍞 Attempting to show Toastify: {title, message}
✅ Toastify notification shown successfully
```

## 🚨 KEMUNGKINAN MASALAH & SOLUSI

### 1. Echo Tidak Setup (No logs)
**Gejala**: Tidak ada log Echo di console
**Solusi**:
```javascript
// Test di console browser:
console.log('Echo:', window.Echo);
console.log('Connection:', window.Echo?.connector?.pusher?.connection?.state);
```

### 2. Event Tidak Diterima (Setup OK tapi no events)
**Gejala**: Setup log muncul, tapi tidak ada "stock.level.changed event received"
**Solusi**:
```powershell
# Cek queue worker
Get-Process | Where-Object {$_.ProcessName -like "*php*"}

# Restart queue worker
php artisan queue:restart
php artisan queue:work
```

### 3. Authentication Error
**Gejala**: Error 403 atau auth failed di console
**Solusi**: Pastikan user sudah login di browser

### 4. Toastify Error (Events OK tapi no toast)
**Gejala**: Events diterima tapi Toastify error
**Solusi**:
```javascript
// Test manual di console browser:
Toastify({text: "Test", duration: 3000}).showToast();
```

## 🔧 EMERGENCY RESTART PROCEDURE

Jika live update masih tidak bekerja:

```powershell
# 1. Stop semua proses
taskkill /f /im php.exe
taskkill /f /im node.exe

# 2. Clear cache
php artisan cache:clear
php artisan config:clear

# 3. Restart layanan
start-stock-alerts.bat

# 4. Test lagi setelah 30 detik
php test_live_update.php
```

## 📱 EXPECTED LIVE UPDATE BEHAVIOR

Saat inventory berubah:
1. 🔄 Halaman Stock Alerts TIDAK perlu di-refresh
2. 🍞 Toastify notification muncul otomatis
3. 📊 Alert baru muncul di top of list
4. 🔢 Counter "unread" bertambah
5. 🟢 Status connection tetap hijau

## 💡 QUICK TEST

Cara tercepat test live update:
```powershell
# Terminal 1: Monitor queue
php artisan queue:work --verbose

# Terminal 2: Trigger test
php test_live_update.php

# Browser: Cek console + lihat halaman
```

**Status: Jika mengikuti step ini dan masih tidak live update, ada masalah spesifik yang perlu di-debug lebih lanjut.**
