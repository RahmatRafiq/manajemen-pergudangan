# 🧪 PANDUAN UJI NOTIFIKASI TOASTIFY

## Status Sistem ✅

✅ **Backend berjalan** - PHP queue worker dan Reverb server aktif  
✅ **Frontend berjalan** - Vite dev server aktif  
✅ **Toastify terintegrasi** - CSS dimuat di `app.css`, komponen siap  
✅ **Stock alerts aktif** - Observer memantau perubahan inventory  
✅ **Database notifikasi** - 695+ notifikasi tersimpan  

## Cara Test Notifikasi Toastify

### 1. Test Manual (Mudah)
1. Buka browser ke: `http://localhost:5173/stock-alerts`
2. Klik tombol **"🧪 Test Toast"** di kanan atas
3. Anda harus melihat notifikasi hijau muncul: "🧪 Test Toast Notification - Toastify bekerja!"

### 2. Test Real-time Alert (Advanced)
1. Pastikan semua layanan berjalan:
   ```powershell
   # Tab 1: Queue worker
   php artisan queue:work
   
   # Tab 2: Reverb server  
   php artisan reverb:start
   
   # Tab 3: Frontend
   npm run dev
   ```

2. Buka `http://localhost:5173/stock-alerts` di browser

3. Jalankan trigger stock alert:
   ```powershell
   php test_stock_trigger.php
   ```

4. Anda harus melihat:
   - 🟢 **Toastify notification** muncul di browser
   - 🔔 **Browser notification** (jika diizinkan)
   - 📱 **Alert baru** di halaman Stock Alerts

## Troubleshooting

### Toastify tidak muncul?
1. **Cek console browser** - buka F12 → Console
2. **Pastikan CSS dimuat** - cari `toastify.css` di Network tab
3. **Test manual dulu** - gunakan tombol "Test Toast"

### Real-time tidak bekerja?
1. **Cek status koneksi** - lihat indikator hijau/merah di halaman
2. **Cek console log** - harus ada log "🔗 Connected to Echo"
3. **Restart layanan** - gunakan `start-stock-alerts.bat`

### Queue jobs gagal?
```powershell
# Cek failed jobs
php artisan queue:failed

# Retry all failed jobs  
php artisan queue:retry all
```

## File yang Sudah Dikonfigurasi

- ✅ `resources/js/hooks/use-stock-alerts.ts` - React hook dengan Toastify
- ✅ `resources/js/pages/StockAlerts.tsx` - Halaman dengan tombol test
- ✅ `resources/css/app.css` - CSS Toastify dimuat
- ✅ `app/Observers/InventoryObserver.php` - Observer untuk trigger
- ✅ `app/Events/StockLevelChanged.php` - Event broadcasting
- ✅ `start-stock-alerts.bat` - Script start semua layanan

## Demo Video Expected
1. 🎬 Buka halaman Stock Alerts
2. 🎯 Klik "Test Toast" → notifikasi hijau muncul
3. 🚨 Trigger real alert → notifikasi merah/orange muncul  
4. 📊 Alert baru muncul di list
5. 🔄 Counter unread bertambah

**Semuanya sudah siap! Silakan test tombol "Test Toast" terlebih dahulu.**
