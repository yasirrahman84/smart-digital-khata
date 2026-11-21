<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private SaleService $sales) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;

        $from = $request->query('from');
        $to = $request->query('to');
        $method = $request->query('payment');
        $customerId = $request->query('customer');
        $status = $request->query('status', 'all');

        $base = Sale::where('user_id', $userId)
            ->when($from, fn ($query) => $query->whereDate('sale_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('sale_date', '<=', $to))
            ->when(in_array($method, [Sale::PAYMENT_CASH, Sale::PAYMENT_UDHAAR], true),
                fn ($query) => $query->where('payment_method', $method))
            ->when($customerId, fn ($query) => $query->where('customer_id', $customerId))
            ->when($status === 'voided', fn ($query) => $query->whereNotNull('voided_at'))
            ->when($status === 'active', fn ($query) => $query->whereNull('voided_at'));

        $summary = [
            'count' => (clone $base)->notVoided()->count(),
            'revenue' => round((float) (clone $base)->notVoided()->sum('total'), 2),
            'cash' => round((float) (clone $base)->notVoided()->where('payment_method', Sale::PAYMENT_CASH)->sum('total'), 2),
            'udhaar' => round((float) (clone $base)->notVoided()->where('payment_method', Sale::PAYMENT_UDHAAR)->sum('total'), 2),
        ];

        $sales = (clone $base)
            ->with('customer')
            ->withCount('items')
            ->latest('sale_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::where('user_id', $userId)->orderBy('name')->get(['id', 'name']);

        return view('sales.index', compact(
            'sales', 'summary', 'customers', 'from', 'to', 'method', 'customerId', 'status'
        ));
    }

    public function create(Request $request)
    {
        $userId = $request->user()->id;

        $products = Product::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'unit', 'sale_price', 'stock_qty', 'low_stock_threshold']);

        $customers = Customer::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);

        $initialCart = collect(old('items', []))
            ->map(function (array $item) use ($products) {
                $product = $products->firstWhere('id', (int) ($item['product_id'] ?? 0));

                if (! $product) {
                    return null;
                }

                return [
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'unit' => $product->unit,
                    'stock' => (float) $product->stock_qty,
                    'unit_price' => (float) ($item['unit_price'] ?? $product->sale_price),
                    'quantity' => (float) ($item['quantity'] ?? 1),
                ];
            })
            ->filter()
            ->values();

        return view('sales.create', [
            'products' => $products,
            'customers' => $customers,
            'initialCart' => $initialCart,
            'initialDiscount' => (float) old('discount', 0),
            'initialPayment' => old('payment_method', Sale::PAYMENT_CASH),
            'initialCustomer' => (string) old('customer_id', ''),
            'initialNotes' => (string) old('notes', ''),
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        $sale = $this->sales->createSale($request->user(), $request->validated());

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Sale {$sale->invoice_no} recorded.");
    }

    public function show(Sale $sale)
    {
        $this->authorize('view', $sale);

        $sale->load(['items', 'customer', 'ledgerEntries']);

        return view('sales.show', compact('sale'));
    }

    public function void(Sale $sale)
    {
        $this->authorize('void', $sale);

        $this->sales->voidSale($sale);

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Sale {$sale->invoice_no} was voided and stock restored.");
    }
}
