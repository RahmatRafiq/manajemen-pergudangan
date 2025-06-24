<?php

namespace App\Events;

use App\Models\Inventory;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockLevelChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inventory;
    public $oldQuantity;
    public $newQuantity;
    public $alertType;

    /**
     * Create a new event instance.
     */
    public function __construct(Inventory $inventory, int $oldQuantity, int $newQuantity, ?string $alertType = null)
    {
        $this->inventory = $inventory;
        $this->oldQuantity = $oldQuantity;
        $this->newQuantity = $newQuantity;
        $this->alertType = $alertType;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('stock-alerts'),
            new PrivateChannel('warehouse.' . $this->inventory->warehouse_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'inventory_id' => $this->inventory->id,
            'product_id' => $this->inventory->product_id,
            'warehouse_id' => $this->inventory->warehouse_id,
            'product_name' => $this->inventory->product->name,
            'warehouse_name' => $this->inventory->warehouse->name,
            'old_quantity' => $this->oldQuantity,
            'new_quantity' => $this->newQuantity,
            'min_stock' => $this->inventory->min_stock,
            'max_stock' => $this->inventory->max_stock,
            'alert_type' => $this->alertType,
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'stock.level.changed';
    }
}
