<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inventory;

echo "Finding first inventory item...\n";
$inventory = Inventory::first();

if (!$inventory) {
    echo "No inventory found!\n";
    exit(1);
}

echo "Current inventory:\n";
echo "- Product: {$inventory->product->name}\n";
echo "- Warehouse: {$inventory->warehouse->name}\n";
echo "- Current quantity: {$inventory->quantity}\n";
echo "- Min stock: {$inventory->min_stock}\n";
echo "- Max stock: {$inventory->max_stock}\n";

echo "\nTriggering low stock alert by setting quantity to 1...\n";
$inventory->update(['quantity' => 1]);

echo "New quantity: {$inventory->fresh()->quantity}\n";
echo "Alert should be triggered now! Check your browser for Toastify notification.\n";
