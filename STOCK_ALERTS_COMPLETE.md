# Stock Alert System Documentation

## Overview
Sistem notifikasi stock alert otomatis untuk Laravel + React yang memberikan peringatan real-time ketika inventory berada di bawah minimum atau di atas maksimum yang ditentukan.

## Features Implemented

### 🎯 Backend Features
- **Stock Level Monitoring**: Otomatis mengecek level inventory terhadap min_stock dan max_stock
- **Multiple Notification Channels**: Database, Email, dan Real-time Broadcasting
- **Observer Pattern**: Automatic alerts saat inventory berubah
- **Manual Commands**: Artisan command untuk pengecekan manual/cron jobs
- **API Testing**: Endpoints untuk testing alert functionality

### 🌟 Frontend Features
- **React Hook**: `useStockAlerts` untuk real-time alert handling
- **Stock Alerts Page**: Dedicated page untuk melihat dan mengelola alerts
- **Toast Notifications**: Real-time toast menggunakan Toastify.js
- **Browser Notifications**: Native browser notifications
- **Alert Center**: Full-featured alert management interface
- **TypeScript Support**: Fully typed dengan strict mode
- **ESLint Compliant**: Clean code standards

## Architecture

### Backend Components

#### 1. **StockAlertNotification** (`app/Notifications/StockAlertNotification.php`)
```php
// Supports multiple channels: database, mail, broadcast
public function via($notifiable): array
{
    return ['database', 'mail', 'broadcast'];
}
```

#### 2. **StockLevelChanged Event** (`app/Events/StockLevelChanged.php`)
```php
// Broadcasting event untuk real-time updates
class StockLevelChanged implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new PrivateChannel('stock-alerts')];
    }
}
```

#### 3. **InventoryObserver** (`app/Observers/InventoryObserver.php`)
```php
// Auto-trigger alerts saat inventory berubah
public function updated(Inventory $inventory): void
{
    $this->checkStockLevels($inventory);
}
```

#### 4. **CheckStockLevels Command** (`app/Console/Commands/CheckStockLevels.php`)
```bash
# Manual/Cron execution
php artisan stock:check-levels
php artisan stock:check-levels --send-notifications
```

#### 5. **StockAlertController** (`app/Http/Controllers/StockAlertController.php`)
- `GET /stock-alerts` - Halaman utama alerts
- `PATCH /stock-alerts/{id}/read` - Mark alert as read
- `PATCH /stock-alerts/read-all` - Mark all as read
- `DELETE /stock-alerts/clear` - Clear all alerts

### Frontend Components

#### 1. **useStockAlerts Hook** (`resources/js/hooks/use-stock-alerts.ts`)
```typescript
export function useStockAlerts() {
    const [state, setState] = useState<UseStockAlertsState>({
        alerts: [],
        unreadCount: 0,
        isConnected: false,
        isListening: false,
        lastUpdated: null,
    });
    // ... real-time functionality
}
```

#### 2. **StockAlerts Page** (`resources/js/pages/StockAlerts.tsx`)
- Full-featured alert management interface
- Search and filtering capabilities
- Real-time updates
- Stats dashboard
- Mark as read/unread functionality

#### 3. **StockAlertCard Component** (`resources/js/components/stock-alert-card.tsx`)
- Individual alert display
- Progress bars for stock levels
- Action buttons
- Responsive design

#### 4. **StockAlertStats Component** (`resources/js/components/stock-alert-stats.tsx`)
- Summary statistics
- Alert type counts
- Visual indicators

## Usage Examples

### Testing Alerts
```bash
# Generate test alerts
php artisan stock:check-levels --send-notifications

# Reset inventory for testing
curl -X POST http://localhost:8000/api/stock-test/reset

# Test low stock
curl -X POST http://localhost:8000/api/stock-test/low-stock \
  -H "Content-Type: application/json" \
  -d '{"inventory_id": 1, "quantity": 5}'

# Test overstock
curl -X POST http://localhost:8000/api/stock-test/overstock \
  -H "Content-Type: application/json" \
  -d '{"inventory_id": 1, "quantity": 1000}'
```

### Frontend Integration
```tsx
import { useStockAlerts } from '@/hooks/use-stock-alerts';

function MyComponent() {
    const { alerts, unreadCount, isConnected, markAsRead, clearAlerts } = useStockAlerts();
    
    return (
        <div>
            <p>Unread alerts: {unreadCount}</p>
            <p>Connection status: {isConnected ? 'Connected' : 'Disconnected'}</p>
            {alerts.map(alert => (
                <div key={alert.id}>
                    {alert.message}
                    <button onClick={() => markAsRead(alert.id)}>Mark as Read</button>
                </div>
            ))}
        </div>
    );
}
```

## Navigation

The system adds a new navigation entry in the sidebar:
- **Stock Alerts** page accessible via `/stock-alerts`
- Shows unread count badge
- Real-time connection status

## System Status

✅ **Completed Features:**
- Backend notification system (Database, Email, Broadcast)
- Real-time alerts via Laravel Echo
- React hooks for alert management
- Dedicated Stock Alerts page
- Toast and browser notifications
- Alert management (mark as read, clear all)
- Search and filtering capabilities
- TypeScript strict mode compliance
- ESLint compliance
- API endpoints for alert management
- Observer pattern for automatic detection
- Manual command for cron jobs

🚀 **Ready for Production:**
- All components tested and working
- Documentation complete
- Code follows best practices
- Error handling implemented
- Security measures in place

## ✅ Issues Resolved

### 1. **Infinite Loop Fixed**
- **Problem**: The React `useStockAlerts` hook was causing infinite re-renders with thousands of console logs
- **Solution**: Fixed dependency arrays and refs to prevent unnecessary re-renders
- **Result**: Hook now mounts only once and functions properly

### 2. **Database Alerts Loading**
- **Added**: `loadAlertsFromDatabase()` function to fetch existing alerts from the backend
- **Integration**: Stock Alerts page now loads and displays stored alerts from the database
- **API**: Stock alerts are fetched from `/stock-alerts` endpoint

### 3. **JSON API Response Fixed**
- **Problem**: The `/stock-alerts` endpoint was returning HTML instead of JSON
- **Solution**: Updated StockAlertController to detect JSON requests and return proper JSON response
- **Result**: React hook can now successfully fetch alerts from database

### 4. **Real-time Updates Working**
- **Echo Integration**: Real-time WebSocket connection established
- **Notifications**: Toastify notifications show for new alerts
- **Browser Notifications**: Desktop notifications work when permission granted

---

**Version**: 1.0.0  
**Last Updated**: June 2025  
**Framework**: Laravel 11 + React 18 + TypeScript
