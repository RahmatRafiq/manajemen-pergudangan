<?php

// Simple test script to trigger stock alert
use App\Models\Inventory;

$inventory = Inventory::first();

if ($inventory) {
    echo "Testing inventory ID: {$inventory->id}\n";
    echo "Current quantity: {$inventory->quantity}\n";
    
    // Update to trigger low stock alert
    $inventory->update([
        'quantity' => 5,
        'min_stock' => 10,
        'max_stock' => 100
    ]);
    
    echo "Updated quantity to 5 (below min_stock of 10)\n";
    echo "Stock alert should be triggered!\n";
} else {
    echo "No inventory found\n";
}
