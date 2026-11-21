<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function __construct(private LowStockService $lowStock) {}

    /**
     * Adjust a product's stock and record the movement.
     *
     * Modes:
     *  - in:  add the given quantity
     *  - out: remove the given quantity (never below zero)
     *  - set: set the stock to an exact count (movement stores the delta)
     */
    public function adjust(Product $product, string $mode, float $quantity, ?string $note = null): StockMovement
    {
        return DB::transaction(function () use ($product, $mode, $quantity, $note) {
            $product = Product::whereKey($product->id)->lockForUpdate()->firstOrFail();

            $current = (float) $product->stock_qty;
            $quantity = round($quantity, 3);

            [$type, $delta] = match ($mode) {
                'in' => [StockMovement::TYPE_IN, $quantity],
                'out' => [StockMovement::TYPE_OUT, -$quantity],
                'set' => [StockMovement::TYPE_ADJUSTMENT, round($quantity - $current, 3)],
                default => throw ValidationException::withMessages(['mode' => 'Choose a valid stock action.']),
            };

            $newStock = round($current + $delta, 3);

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'quantity' => "You only have {$current} {$product->unit} in stock.",
                ]);
            }

            if ($delta === 0.0 && $mode !== 'set') {
                throw ValidationException::withMessages([
                    'quantity' => 'Enter a quantity greater than zero.',
                ]);
            }

            $product->forceFill(['stock_qty' => $newStock])->save();

            $movement = StockMovement::create([
                'user_id' => $product->user_id,
                'product_id' => $product->id,
                'type' => $type,
                'quantity' => $mode === 'set' ? $delta : abs($quantity),
                'note' => $note ?: $this->defaultNote($mode, $current, $newStock, $product->unit),
            ]);

            $this->lowStock->evaluate($product->fresh());

            return $movement;
        });
    }

    private function defaultNote(string $mode, float $before, float $after, string $unit): string
    {
        return match ($mode) {
            'in' => 'Stock added',
            'out' => 'Stock removed',
            default => "Count set to {$after} {$unit} (was {$before})",
        };
    }
}
