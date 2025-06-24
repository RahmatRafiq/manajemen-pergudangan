<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Inventory;

class StockAlertNotification extends Notification
{
    use Queueable;

    protected $inventory;
    protected $alertType;
    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Inventory $inventory, string $alertType)
    {
        $this->inventory = $inventory;
        $this->alertType = $alertType;
        $this->generateMessage();
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $subject = $this->alertType === 'low_stock' ? 'Peringatan Stok Rendah' : 'Peringatan Stok Berlebih';
        
        return (new MailMessage)
            ->subject($subject)
            ->line($this->message)
            ->line("Produk: {$this->inventory->product->name}")
            ->line("Gudang: {$this->inventory->warehouse->name}")
            ->line("Stok Saat Ini: {$this->inventory->quantity}")
            ->line("Stok Minimum: {$this->inventory->min_stock}")
            ->line("Stok Maksimum: {$this->inventory->max_stock}")
            ->action('Lihat Detail Inventory', url('/admin/inventory'))
            ->line('Mohon segera lakukan tindakan yang diperlukan.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => $this->alertType,
            'message' => $this->message,
            'inventory_id' => $this->inventory->id,
            'product_name' => $this->inventory->product->name,
            'warehouse_name' => $this->inventory->warehouse->name,
            'current_quantity' => $this->inventory->quantity,
            'min_stock' => $this->inventory->min_stock,
            'max_stock' => $this->inventory->max_stock,
            'product_id' => $this->inventory->product_id,
            'warehouse_id' => $this->inventory->warehouse_id,
        ];
    }    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => $this->alertType,
            'message' => $this->message,
            'inventory_id' => $this->inventory->id,
            'product_name' => $this->inventory->product->name,
            'warehouse_name' => $this->inventory->warehouse->name,
            'current_quantity' => $this->inventory->quantity,
            'min_stock' => $this->inventory->min_stock,
            'max_stock' => $this->inventory->max_stock,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'stock.alert';    }

    /**
     * Generate appropriate message based on alert type.
     */
    private function generateMessage(): void
    {
        if ($this->alertType === 'low_stock') {
            $this->message = "Stok produk {$this->inventory->product->name} di gudang {$this->inventory->warehouse->name} telah mencapai batas minimum. Stok saat ini: {$this->inventory->quantity}, minimum: {$this->inventory->min_stock}.";
        } elseif ($this->alertType === 'overstock') {
            $this->message = "Stok produk {$this->inventory->product->name} di gudang {$this->inventory->warehouse->name} melebihi batas maksimum. Stok saat ini: {$this->inventory->quantity}, maksimum: {$this->inventory->max_stock}.";
        }
    }
}
