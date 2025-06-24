<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Inventory;

echo "Triggering stock change for queue monitoring...\n";

$inventory = Inventory::first();
$inventory->update(['quantity' => 2]);

echo "Stock changed! Queue job should be dispatched.\n";
echo "Monitor with: php artisan queue:work --verbose\n";
