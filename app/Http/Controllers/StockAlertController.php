<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StockAlertController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        $notifications = $user->notifications()
            ->where('type', 'App\\Notifications\\StockAlertNotification')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $alerts = $notifications->map(function ($notification) {
            $data = $notification->data;
            return [
                'id' => $notification->id,
                'type' => $data['type'] ?? 'low_stock',
                'message' => $data['message'] ?? '',
                'inventory_id' => $data['inventory_id'] ?? 0,
                'product_name' => $data['product_name'] ?? '',
                'warehouse_name' => $data['warehouse_name'] ?? '',
                'current_quantity' => $data['current_quantity'] ?? 0,
                'min_stock' => $data['min_stock'] ?? null,
                'max_stock' => $data['max_stock'] ?? null,
                'product_id' => $data['product_id'] ?? 0,
                'warehouse_id' => $data['warehouse_id'] ?? 0,
                'timestamp' => $notification->created_at->toISOString(),
                'created_at' => $notification->created_at->toISOString(),
                'read_at' => $notification->read_at,
            ];
        });

        $unreadCount = $notifications->whereNull('read_at')->count();

        if ($request->expectsJson()) {
            return response()->json([
                'alerts' => $alerts,
                'unread_count' => $unreadCount,
                'total_alerts' => $alerts->count(),
            ]);
        }

        return Inertia::render('StockAlerts', [
            'initialAlerts' => $alerts,
            'totalAlerts' => $alerts->count(),
        ]);
    }

    public function markAsRead(Request $request, string $alertId)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $updated = DB::table('notifications')
            ->where('id', $alertId)
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $user->id)
            ->where('type', 'App\\Notifications\\StockAlertNotification')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        if ($updated === 0) {
            return response()->json(['error' => 'Notification not found or already read'], 404);
        }

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $updatedCount = DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $user->id)
            ->where('type', 'App\\Notifications\\StockAlertNotification')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'marked_count' => $updatedCount
        ]);
    }

    public function clear(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $deletedCount = DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $user->id)
            ->where('type', 'App\\Notifications\\StockAlertNotification')
            ->delete();

        return response()->json([
            'success' => true,
            'deleted_count' => $deletedCount
        ]);
    }

    public function getAlertsApi(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $notifications = $user->notifications()
            ->where('type', 'App\\Notifications\\StockAlertNotification')
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        $alerts = $notifications->map(function ($notification) {
            $data = $notification->data;
            return [
                'id' => $notification->id,
                'type' => $data['type'] ?? 'low_stock',
                'message' => $data['message'] ?? '',
                'inventory_id' => $data['inventory_id'] ?? 0,
                'product_name' => $data['product_name'] ?? '',
                'warehouse_name' => $data['warehouse_name'] ?? '',
                'current_quantity' => $data['current_quantity'] ?? 0,
                'min_stock' => $data['min_stock'] ?? null,
                'max_stock' => $data['max_stock'] ?? null,
                'product_id' => $data['product_id'] ?? 0,
                'warehouse_id' => $data['warehouse_id'] ?? 0,
                'timestamp' => $notification->created_at->toISOString(),
                'created_at' => $notification->created_at->toISOString(),
                'read_at' => $notification->read_at,
            ];
        });

        return response()->json($alerts);
    }
}
