<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustStockRequest;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Services\LowStockService;
use App\Services\StockService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private StockService $stock,
        private LowStockService $lowStock,
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $search = trim((string) $request->query('q'));
        $filter = (string) $request->query('filter', 'all');
        $sort = (string) $request->query('sort', 'name');

        $query = Product::where('user_id', $userId)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            });

        match ($filter) {
            'low' => $query->where('is_active', true)->whereColumn('stock_qty', '<=', 'low_stock_threshold'),
            'archived' => $query->where('is_active', false),
            default => $query->where('is_active', true),
        };

        match ($sort) {
            'stock' => $query->orderBy('stock_qty'),
            'value' => $query->orderByRaw('stock_qty * purchase_price desc'),
            default => $query->orderBy('name'),
        };

        $products = $query->paginate(15)->withQueryString();

        $active = Product::where('user_id', $userId)->where('is_active', true);

        $summary = [
            'items' => (clone $active)->count(),
            'low' => (clone $active)->whereColumn('stock_qty', '<=', 'low_stock_threshold')->count(),
            'value' => round((clone $active)->get()->sum(fn (Product $product) => $product->stockValue()), 2),
        ];

        return view('products.index', compact('products', 'summary', 'search', 'filter', 'sort'));
    }

    public function create()
    {
        return view('products.create', [
            'product' => new Product([
                'unit' => 'piece',
                'stock_qty' => 0,
                'low_stock_threshold' => 5,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create([
            'user_id' => $request->user()->id,
            'name' => $request->validated('name'),
            'sku' => $request->validated('sku'),
            'unit' => $request->validated('unit'),
            'purchase_price' => $request->validated('purchase_price'),
            'sale_price' => $request->validated('sale_price'),
            'stock_qty' => $request->validated('stock_qty'),
            'low_stock_threshold' => $request->validated('low_stock_threshold'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $this->lowStock->evaluate($product);

        return redirect()
            ->route('products.show', $product)
            ->with('success', "{$product->name} was added to your stock.");
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        $movements = $product->stockMovements()
            ->latest()
            ->paginate(20);

        $sold = $product->saleItems()
            ->with('sale')
            ->latest()
            ->limit(5)
            ->get();

        return view('products.show', compact('product', 'movements', 'sold'));
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update([
            'name' => $request->validated('name'),
            'sku' => $request->validated('sku'),
            'unit' => $request->validated('unit'),
            'purchase_price' => $request->validated('purchase_price'),
            'sale_price' => $request->validated('sale_price'),
            'low_stock_threshold' => $request->validated('low_stock_threshold'),
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->lowStock->evaluate($product->fresh());

        return redirect()
            ->route('products.show', $product)
            ->with('success', 'Product updated.');
    }

    /**
     * Archive the product. It disappears from the POS and low-stock alerts
     * but keeps its sale history intact.
     */
    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->update(['is_active' => false]);

        return redirect()
            ->route('products.index')
            ->with('success', "{$product->name} was archived.");
    }

    public function activate(Product $product)
    {
        $this->authorize('update', $product);

        $product->update(['is_active' => true]);

        $this->lowStock->evaluate($product->fresh());

        return redirect()
            ->route('products.show', $product)
            ->with('success', "{$product->name} is active again.");
    }

    public function adjust(AdjustStockRequest $request, Product $product)
    {
        $movement = $this->stock->adjust(
            $product,
            $request->validated('mode'),
            (float) $request->validated('quantity'),
            $request->validated('note'),
        );

        $product->refresh();

        $delta = $movement->signedQuantity();
        $sign = $delta >= 0 ? '+' : '−';

        return redirect()
            ->route('products.show', $product)
            ->with('success', "Stock updated ({$sign}".rtrim(rtrim(number_format(abs($delta), 3), '0'), '.')." {$product->unit}). New stock: ".rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.').'.');
    }
}
