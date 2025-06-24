# Stock Alerts System - Troubleshooting Guide

## Current Status
✅ **Backend**: Notifications being sent and stored in database (565 records)  
❌ **Frontend**: Echo showing "Disconnected" status  
❌ **Data Loading**: Alerts not displaying from database  

## Issues Found & Solutions

### 1. Echo Connection "Disconnected"

**Problem**: Frontend shows "Disconnected" even though Reverb server is running

**Debugging Steps**:
1. Open browser console on `/stock-alerts` page
2. Check if Echo is loaded: `console.log(window.Echo)`
3. Check WebSocket connection: `console.log(window.Echo.connector)`

**Solution A - Check Echo Import**:
- ✅ Added `import './echo';` to `app.tsx`
- ✅ Reverb server running on port 8080
- ✅ Environment variables configured

**Solution B - Browser Console Debug**:
```javascript
// In browser console, test Echo connection
console.log('Echo:', window.Echo);
console.log('Connection:', window.Echo?.connector?.pusher?.connection?.state);

// Test manual connection
window.Echo.private('stock-alerts')
  .listen('stock.alert', (data) => {
    console.log('Alert received:', data);
  });
```

### 2. Database Alerts Not Loading

**Problem**: Existing notifications in database not showing in UI

**Check**: Database has 565 notifications
```bash
php artisan tinker --execute="echo DB::table('notifications')->count();"
```

**Solution**: Ensure user is logged in and controller loads their notifications

### 3. Authentication & Role Issues

**Problem**: User might not have proper role or not be logged in

**Check Current User**:
```bash
# Login to the application first at http://127.0.0.1:8000
# Then access /stock-alerts page
```

## Testing Steps

### Step 1: Basic Login Test
1. Go to `http://127.0.0.1:8000`
2. Login with any account
3. Navigate to `http://127.0.0.1:8000/stock-alerts`
4. Check if page loads without errors

### Step 2: Echo Debug Test
1. Open browser console
2. Run: `console.log(window.Echo)`
3. Should show Echo object, not undefined

### Step 3: Manual Alert Test
1. In browser console, run:
```javascript
// Test if we can manually trigger alert
window.useStockAlerts = () => {
  console.log('Testing manual alert');
};
```

### Step 4: Backend Test
```bash
# Send test alerts
php artisan stock:check-levels --send-notifications

# Check if notifications created
php artisan tinker --execute="echo 'Count: ' . DB::table('notifications')->count();"
```

## Quick Fixes

### Fix 1: Ensure User Login
1. Go to root URL and login
2. Navigate to stock alerts page
3. Check authentication status

### Fix 2: Force Echo Reconnection
Add to frontend hook:
```typescript
// Add debugging to useStockAlerts hook
useEffect(() => {
  console.log('🔄 useStockAlerts hook mounted');
  console.log('📊 Echo available:', !!window.Echo);
  console.log('🔌 Starting connection...');
}, []);
```

### Fix 3: Show Database Alerts
Modify controller to always return notifications for current user:
```php
// In StockAlertController@index
$notifications = auth()->user()
  ->notifications()
  ->where('type', StockAlertNotification::class)
  ->latest()
  ->take(50)
  ->get();
```

## Expected Behavior

### When Working:
1. ✅ Status shows "Connected" 
2. ✅ Existing alerts from DB displayed
3. ✅ New alerts appear in real-time
4. ✅ Toast notifications show
5. ✅ Browser notifications (if permitted)

### Current Behavior:
1. ❌ Status shows "Disconnected"
2. ❌ No alerts displayed
3. ❌ No real-time updates
4. ❌ No toast notifications

## Next Actions

1. **Immediate**: Debug Echo connection in browser console
2. **Fallback**: Load alerts from database without real-time
3. **Real-time**: Fix WebSocket connection once basic display works

## Files to Check

### Frontend:
- `resources/js/echo.js` - Echo configuration
- `resources/js/app.tsx` - Echo import
- `resources/js/hooks/use-stock-alerts.ts` - Hook implementation
- `resources/js/pages/StockAlerts.tsx` - Page component

### Backend:
- `app/Http/Controllers/StockAlertController.php` - Data loading
- `routes/channels.php` - WebSocket authorization
- `config/broadcasting.php` - Broadcast configuration
- `.env` - Environment variables

### Servers:
- Laravel: `php artisan serve` (port 8000)
- Reverb: `php artisan reverb:start` (port 8080)  
- Queue: `php artisan queue:work`
- Vite: `npm run dev`
