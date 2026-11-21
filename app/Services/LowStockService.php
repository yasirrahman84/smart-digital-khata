<?php

namespace App\Services;

use App\Models\Product;
use App\Notifications\LowStockNotification;

class LowStockService
{
    /**
     * Notify the merchant once each time a product crosses below its threshold.
     * When stock recovers the marker is cleared, so the next crossing alerts again.
     */
    public function evaluate(Product $product): void
    {
        if (! $product->is_active) {
            return;
        }

        if ($product->isLowStock()) {
            if ($product->low_stock_notified_at === null) {
                $product->user->notify(new LowStockNotification($product));

                $product->forceFill(['low_stock_notified_at' => now()])->save();
            }

            return;
        }

        if ($product->low_stock_notified_at !== null) {
            $product->forceFill(['low_stock_notified_at' => null])->save();
        }
    }
}
