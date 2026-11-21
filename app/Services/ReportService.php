<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\LedgerEntry;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Everything the dashboard needs, in one place so each number is easy to trace.
     *
     * @return array<string, mixed>
     */
    public function dashboard(User $user): array
    {
        $receivable = $this->totalReceivable($user);
        $todaySales = $user->sales()->notVoided()->whereDate('sale_date', today());

        $monthCashSales = (float) $user->sales()
            ->notVoided()
            ->where('payment_method', Sale::PAYMENT_CASH)
            ->whereBetween('sale_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total');

        $monthPayments = (float) $user->ledgerEntries()
            ->where('type', LedgerEntry::TYPE_PAYMENT)
            ->whereBetween('entry_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');

        $lowStockCount = $user->products()
            ->where('is_active', true)
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->count();

        $topDebtors = $user->customers()
            ->withBalance()
            ->get()
            ->filter(fn (Customer $customer) => $customer->balance > 0)
            ->sortByDesc('balance')
            ->take(5)
            ->values();

        $lowStockProducts = $user->products()
            ->where('is_active', true)
            ->whereColumn('stock_qty', '<=', 'low_stock_threshold')
            ->orderBy('stock_qty')
            ->limit(5)
            ->get();

        return [
            'receivable' => $receivable,
            'todayCount' => (clone $todaySales)->count(),
            'todayTotal' => round((float) (clone $todaySales)->sum('total'), 2),
            'cashCollected' => round($monthCashSales + $monthPayments, 2),
            'cashSales' => round($monthCashSales, 2),
            'khataPayments' => round($monthPayments, 2),
            'lowStockCount' => $lowStockCount,
            'topDebtors' => $topDebtors,
            'lowStockProducts' => $lowStockProducts,
            'chart' => $this->weeklyChart($user),
            'activity' => $this->activity($user),
        ];
    }

    /**
     * Total owed by customers (advances are ignored, not netted off).
     */
    public function totalReceivable(User $user): float
    {
        return round(
            $user->customers()
                ->withBalance()
                ->get()
                ->filter(fn (Customer $customer) => $customer->balance > 0)
                ->sum('balance'),
            2
        );
    }

    /**
     * Last seven days of sales split into cash and udhaar.
     *
     * @return array{labels: array<int, string>, cash: array<int, float>, udhaar: array<int, float>}
     */
    public function weeklyChart(User $user): array
    {
        $labels = [];
        $cash = [];
        $udhaar = [];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo)->startOfDay();
            $daySales = $user->sales()->notVoided()->whereDate('sale_date', $date->toDateString());

            $labels[] = $date->format('D');
            $cash[] = round((float) (clone $daySales)->where('payment_method', Sale::PAYMENT_CASH)->sum('total'), 2);
            $udhaar[] = round((float) (clone $daySales)->where('payment_method', Sale::PAYMENT_UDHAAR)->sum('total'), 2);
        }

        return compact('labels', 'cash', 'udhaar');
    }

    /**
     * Latest sales and manual khata entries, newest first.
     */
    public function activity(User $user, int $limit = 8): Collection
    {
        $sales = $user->sales()
            ->notVoided()
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Sale $sale) => [
                'at' => $sale->created_at,
                'kind' => 'sale',
                'title' => "Sale {$sale->invoice_no}",
                'detail' => $sale->isUdhaar() ? 'Udhaar sale' : 'Cash sale',
                'amount' => (float) $sale->total,
                'url' => route('sales.show', $sale),
            ]);

        $entries = $user->ledgerEntries()
            ->with('customer')
            ->whereNull('sale_id')
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (LedgerEntry $entry) => [
                'at' => $entry->created_at,
                'kind' => $entry->isUdhaar() ? 'udhaar' : 'payment',
                'title' => $entry->customer?->name ?? 'Customer',
                'detail' => $entry->isUdhaar() ? 'Udhaar recorded' : 'Payment received',
                'amount' => (float) $entry->amount,
                'url' => $entry->customer ? route('customers.show', $entry->customer) : null,
            ]);

        return $sales->merge($entries)->sortByDesc('at')->take($limit)->values();
    }

    /**
     * Customers who owe money, with the age of their oldest unpaid debt.
     *
     * Payments are applied FIFO (oldest debt first). The opening balance counts
     * as the oldest debt, dated from when the customer was added.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function receivables(User $user): Collection
    {
        return $user->customers()
            ->withBalance()
            ->get()
            ->map(fn (Customer $customer) => $this->receivableRow($customer))
            ->filter(fn (array $row) => $row['balance'] > 0)
            ->sortByDesc('balance')
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function receivableRow(Customer $customer): array
    {
        $queue = [];

        $opening = (float) $customer->opening_balance;

        if ($opening > 0) {
            $queue[] = [
                'date' => $customer->created_at->copy()->startOfDay(),
                'remaining' => $opening,
            ];
        }

        $udhaarEntries = $customer->ledgerEntries()
            ->where('type', LedgerEntry::TYPE_UDHAAR)
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get(['amount', 'entry_date']);

        foreach ($udhaarEntries as $entry) {
            $queue[] = [
                'date' => $entry->entry_date->copy()->startOfDay(),
                'remaining' => (float) $entry->amount,
            ];
        }

        $payments = (float) $customer->ledgerEntries()
            ->where('type', LedgerEntry::TYPE_PAYMENT)
            ->sum('amount');

        foreach ($queue as $index => $debt) {
            if ($payments <= 0) {
                break;
            }

            $applied = min($debt['remaining'], $payments);
            $queue[$index]['remaining'] = round($debt['remaining'] - $applied, 2);
            $payments = round($payments - $applied, 2);
        }

        $oldest = null;

        foreach ($queue as $debt) {
            if ($debt['remaining'] > 0.004) {
                $oldest = $debt['date'];
                break;
            }
        }

        $ageDays = $oldest ? (int) $oldest->diffInDays(now()->startOfDay()) : 0;

        return [
            'customer' => $customer,
            'balance' => $customer->balance,
            'oldest_date' => $oldest,
            'age_days' => $ageDays,
            'bucket' => $ageDays <= 30 ? '0-30' : ($ageDays <= 60 ? '31-60' : '60+'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function salesReport(User $user, ?string $from = null, ?string $to = null): array
    {
        $from = $from ?: now()->startOfMonth()->toDateString();
        $to = $to ?: now()->toDateString();

        $base = $user->sales()
            ->notVoided()
            ->whereDate('sale_date', '>=', $from)
            ->whereDate('sale_date', '<=', $to);

        $count = (clone $base)->count();
        $revenue = round((float) (clone $base)->sum('total'), 2);

        $summary = [
            'count' => $count,
            'revenue' => $revenue,
            'discount' => round((float) (clone $base)->sum('discount'), 2),
            'cash' => round((float) (clone $base)->where('payment_method', Sale::PAYMENT_CASH)->sum('total'), 2),
            'udhaar' => round((float) (clone $base)->where('payment_method', Sale::PAYMENT_UDHAAR)->sum('total'), 2),
            'average' => $count > 0 ? round($revenue / $count, 2) : 0.0,
        ];

        $topProducts = SaleItem::query()
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.user_id', $user->id)
            ->whereNull('sales.voided_at')
            ->whereDate('sales.sale_date', '>=', $from)
            ->whereDate('sales.sale_date', '<=', $to)
            ->selectRaw('sale_items.product_name, SUM(sale_items.quantity) AS quantity, SUM(sale_items.line_total) AS revenue')
            ->groupBy('sale_items.product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $daily = (clone $base)
            ->selectRaw('DATE(sale_date) AS day, COUNT(*) AS sale_count, SUM(total) AS revenue')
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('day')
            ->get();

        $sales = (clone $base)
            ->with('customer')
            ->latest('sale_date')
            ->latest('id')
            ->get();

        return compact('from', 'to', 'summary', 'topProducts', 'daily', 'sales');
    }

    /**
     * @return array<string, mixed>
     */
    public function stockReport(User $user): array
    {
        $products = $user->products()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return [
            'products' => $products,
            'summary' => [
                'items' => $products->count(),
                'value' => round($products->sum(fn (Product $product) => $product->stockValue()), 2),
                'low' => $products->filter(fn (Product $product) => $product->isLowStock())->count(),
            ],
        ];
    }
}
