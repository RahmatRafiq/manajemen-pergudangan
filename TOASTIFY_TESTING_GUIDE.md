# 🍞 Panduan Testing Toastify Notifications

## Langkah 1: Start Required Services

Jalankan script berikut:
```bash
start-stock-alerts.bat
```

Atau manual di 3 terminal berbeda:
```bash
# Terminal 1
php artisan queue:work --verbose

# Terminal 2  
php artisan reverb:start

# Terminal 3
npm run dev
```

## Langkah 2: Test Toastify

1. **Buka browser** ke `http://localhost:8000/stock-alerts`
2. **Klik tombol "🧪 Test Toast"** - ini akan menguji Toastify langsung
3. **Jika muncul notification toast**, berarti Toastify bekerja!

## Langkah 3: Test Real-time Alerts

1. **Buka tab baru** ke `http://localhost:8000/stock-transaction/create`
2. **Buat transaksi OUT** dengan quantity yang membuat stok < min_stock
3. **Kembali ke tab Stock Alerts** - seharusnya muncul toast notification real-time

## Debugging Console

Buka **Developer Tools (F12)** dan lihat Console tab:

### ✅ Yang Harus Muncul:
```
🔄 useStockAlerts hook mounted
📊 Echo available: true
🌍 Window Echo object: E {options: {…}, connector: o}
Connected to stock alerts
```

### 📢 Saat Ada Alert Baru:
```
📢 Stock alert received via Echo: {...}
🍞 Showing Toastify notification: {...}
```

## Troubleshooting

### ❌ Jika Toast Test Tidak Muncul:
- CSS Toastify tidak loaded
- Ada error JavaScript

### ❌ Jika Real-time Tidak Bekerja:
- Queue worker tidak jalan
- Reverb server tidak jalan  
- Echo connection failed

### ❌ Jika Echo Object Undefined:
- File echo.js tidak dimuat
- Broadcasting config salah

## Test Manual Cepat

Buka console browser dan jalankan:
```javascript
// Test Toastify langsung
Toastify({
    text: "Test toast manual!",
    duration: 3000,
    style: { background: "green" }
}).showToast();

// Test Echo connection
console.log("Echo available:", !!window.Echo);
```

## Expected Flow

1. **User buat transaksi** → Inventory quantity berubah
2. **InventoryObserver triggered** → Log: "Inventory quantity changed"  
3. **Notification sent** → Log: "Stock alert notifications sent"
4. **Broadcasting queued** → Queue job created
5. **Queue worker processes** → Broadcasting via Reverb
6. **Frontend receives** → Echo event logged
7. **Toast appears** → Toastify notification shown

Setiap step harus bekerja untuk notifikasi muncul!
