<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Stock alert channels
Broadcast::channel('stock-alerts', function ($user) {
    // Allow all authenticated users to listen to stock alerts
    return true; // You can add role checking here if needed: $user->hasRole('admin')
});

Broadcast::channel('warehouse.{warehouseId}', function ($user, $warehouseId) {
    // Users can listen to alerts for specific warehouses
    return true; // You can add more specific authorization logic here
});
