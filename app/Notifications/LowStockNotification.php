<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    use Queueable;

    public function __construct(public Product $product) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'stock_qty' => (float) $this->product->stock_qty,
            'threshold' => (float) $this->product->low_stock_threshold,
            'unit' => $this->product->unit,
            'message' => "{$this->product->name} is low — {$this->product->stock_qty} {$this->product->unit} left.",
        ];
    }
}
