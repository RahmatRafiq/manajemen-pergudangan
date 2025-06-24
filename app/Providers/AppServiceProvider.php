<?php
namespace App\Providers;

use App\Models\Inventory;
use App\Observers\ActivityObserver;
use App\Observers\InventoryObserver;
use Illuminate\Support\ServiceProvider;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Broadcast;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Activity::observe(ActivityObserver::class);
        Inventory::observe(InventoryObserver::class);
        
        Activity::created(function ($activity) {
            broadcast(new \App\Events\ActivityLogCreated($activity));
        });
    }
}
