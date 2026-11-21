<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SaleService
{
    public function __construct(
        private KhataService $khata,
        private LowStockService $lowStock,
    ) {}

    /**
     * Create a sale atomically: line items, stock movements, udhaar entry,
     * invoice number and low-stock checks all happen in one transaction.
     *
     * @param  array{items: array<int, array{product_id: int|string, quantity: float|string, unit_price: float|string}>, payment_method: string, customer_id?: int|string|null, discount?: float|string, notes?: string|null, sale_date?: mixed}  $data
     */
    public function createSale(User $user, array $data): Sale
    {
        return DB::transaction(function () use ($user, $data) {
            $items = collect($data['items'])->values();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Add at least one product to the sale.']);
            }

            $products = Product::where('user_id', $user->id)
                ->whereIn('id', $items->pluck('product_id')->unique())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $prepared = [];

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'One of the selected products no longer exists.']);
                }

                $quantity = round((float) $item['quantity'], 3);
                $unitPrice = round((float) $item['unit_price'], 2);

                if ($quantity <= 0) {
                    throw ValidationException::withMessages(['items' => "Quantity for {$product->name} must be greater than zero."]);
                }

                if ($quantity > (float) $product->stock_qty) {
                    throw ValidationException::withMessages([
                        'items' => "Not enough stock for {$product->name}. Only {$product->stock_qty} {$product->unit} left.",
                    ]);
                }

                $lineTotal = round($quantity * $unitPrice, 2);
                $subtotal += $lineTotal;

                $prepared[] = compact('product', 'quantity', 'unitPrice', 'lineTotal');
            }

            $subtotal = round($subtotal, 2);
            $discount = min(round((float) ($data['discount'] ?? 0), 2), $subtotal);
            $total = round($subtotal - $discount, 2);

            $sale = Sale::create([
                'user_id' => $user->id,
                'customer_id' => $data['customer_id'] ?? null,
                'invoice_no' => 'TMP-'.Str::random(20),
                'sale_date' => $data['sale_date'] ?? now(),
                'subtotal' => $subtotal,
                'discount' => $discount,
                'total' => $total,
                'payment_method' => $data['payment_method'],
                'notes' => $data['notes'] ?? null,
            ]);

            $sale->forceFill([
                'invoice_no' => 'INV-'.str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT),
            ])->save();

            foreach ($prepared as $row) {
                $sale->items()->create([
                    'product_id' => $row['product']->id,
                    'product_name' => $row['product']->name,
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unitPrice'],
                    'line_total' => $row['lineTotal'],
                ]);

                $row['product']->decrement('stock_qty', $row['quantity']);

                StockMovement::create([
                    'user_id' => $user->id,
                    'product_id' => $row['product']->id,
                    'sale_id' => $sale->id,
                    'type' => StockMovement::TYPE_OUT,
                    'quantity' => $row['quantity'],
                    'note' => "Sale {$sale->invoice_no}",
                ]);
            }

            if ($sale->isUdhaar()) {
                if (! $sale->customer_id) {
                    throw ValidationException::withMessages(['customer_id' => 'Choose a customer for an udhaar sale.']);
                }

                $this->khata->postEntry($sale->customer, [
                    'sale_id' => $sale->id,
                    'type' => LedgerEntry::TYPE_UDHAAR,
                    'amount' => $total,
                    'entry_date' => $sale->sale_date->toDateString(),
                    'description' => "Sale {$sale->invoice_no}",
                ]);
            }

            foreach ($prepared as $row) {
                $this->lowStock->evaluate($row['product']->fresh());
            }

            return $sale->load('items', 'customer');
        });
    }

    /**
     * Void a sale: restore stock, reverse the linked khata entry and keep the
     * record for the audit trail (reports ignore voided sales).
     */
    public function voidSale(Sale $sale): Sale
    {
        if ($sale->isVoided()) {
            throw ValidationException::withMessages(['sale' => 'This sale has already been voided.']);
        }

        return DB::transaction(function () use ($sale) {
            $sale->load('items.product');

            foreach ($sale->items as $item) {
                if (! $item->product) {
                    continue;
                }

                $item->product->increment('stock_qty', $item->quantity);

                StockMovement::create([
                    'user_id' => $sale->user_id,
                    'product_id' => $item->product_id,
                    'sale_id' => $sale->id,
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => $item->quantity,
                    'note' => "Void {$sale->invoice_no}",
                ]);
            }

            $sale->ledgerEntries()->delete();
            $sale->forceFill(['voided_at' => now()])->save();

            foreach ($sale->items as $item) {
                if ($item->product) {
                    $this->lowStock->evaluate($item->product->fresh());
                }
            }

            return $sale->refresh();
        });
    }
}
