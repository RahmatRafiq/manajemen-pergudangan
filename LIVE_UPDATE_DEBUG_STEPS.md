# 🔍 LANGKAH DEBUGGING LIVE UPDATE

## 📋 CHECKLIST SISTEMATIS

### 1. ✅ Pastikan Layanan Berjalan
```powershell
# Cek proses yang berjalan
Get-Process | Where-Object {$_.ProcessName -like "*php*" -or $_.ProcessName -like "*node*"}
```
Harus ada:
- `php.exe` (queue worker) 
- `php.exe` (reverb server)
- `node.exe` (vite dev server)

### 2. 🌐 Buka Browser & Setup Monitoring
```
1. Buka: http://localhost:5173/stock-alerts
2. F12 → Console tab
3. Clear console (Ctrl+L)
4. Refresh halaman
```

### 3. 🔍 Cek Initial Logs
Harus muncul di console:
```
🔄 StockAlerts: allAlerts updated, count: [number]
🔍 StockAlerts: Filtering alerts, total: [number]  
📊 StockAlerts: Filtered alerts, count: [number]
🔗 Setting up Echo listeners...
✅ Echo listeners setup complete
🟢 Connected to stock alerts channel
```

### 4. 🚨 Trigger Test
Di terminal baru:
```powershell
php trigger_queue.php
```

### 5. 📊 Monitor Queue Processing
Di terminal lain:
```powershell
php artisan queue:work --once --verbose
```
Harus muncul: `BroadcastNotificationCreated ... DONE`

### 6. 🎯 Cek Event Reception di Browser
Console harus menampilkan (dalam urutan):
```
📊 stock.level.changed event received: {data}
🎯 Alert type detected: low_stock
✅ Creating alert from stock level change
📤 Calling addAlert with: {alert}
🚨 addAlert called with: {alert}
🔄 StockAlerts: allAlerts updated, count: [number+1]
🔍 StockAlerts: Filtering alerts, total: [number+1]
📊 StockAlerts: Filtered alerts, count: [number+1]
🎯 StockAlerts: useEffect triggered, running filterAlerts
🍞 Attempting to show Toastify: {title, message}
✅ Toastify notification shown successfully
```

### 7. ✅ Visual Confirmation
Harus terlihat:
- 🍞 Toastify popup muncul
- 📱 Alert baru di top of list (tanpa refresh)
- 🔢 Counter unread bertambah
- 🟢 Status connection tetap hijau

## 🚨 JIKA STEP GAGAL:

### Tidak ada log Echo (Step 3)
```powershell
# Restart Reverb
php artisan reverb:start
```

### Queue tidak diproses (Step 5) 
```powershell
# Restart queue worker
php artisan queue:restart
php artisan queue:work
```

### Event tidak diterima (Step 6)
```javascript
// Test di console browser:
console.log('Echo state:', window.Echo?.connector?.pusher?.connection?.state);
```

### UI tidak update (Step 7)
- Cek apakah log React component muncul
- Refresh halaman dan coba lagi

## 🎯 QUICK DEBUG COMMAND

Satu command untuk test semuanya:
```powershell
php trigger_queue.php && timeout 2 && php artisan queue:work --once --verbose
```

**Ikuti step ini dan report di step mana yang gagal!**
