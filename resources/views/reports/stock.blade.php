<x-app-layout>
    <x-slot name="title">Stock report</x-slot>
    <x-slot name="subtitle">Current stock valued at purchase price</x-slot>

    @include('reports._tabs', ['current' => 'stock'])

    <div class="mt-5 flex flex-wrap items-center justify-between gap-2">
        <div class="grid flex-1 gap-4 sm:grid-cols-3">
            <x-stat-card label="Active products" :value="$summary['items']" tone="sky" icon="cube" />
            <x-stat-card label="Stock value" :value="'Rs '.number_format($summary['value'])" tone="emerald" icon="banknotes" hint="At purchase price" />
            <x-stat-card label="Low stock" :value="$summary['low']" tone="rose" icon="exclamation-triangle" />
        </div>
    </div>

    <div class="no-print mt-4 flex justify-end gap-2">
        <a href="{{ route('reports.stock.csv') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
            Export CSV
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            <x-heroicon-o-printer class="h-4 w-4" />
            Print
        </button>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        @if ($products->isEmpty())
            <x-empty-state icon="cube" title="No products yet" description="Add products to see a stock valuation here." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-3">Product</th>
                            <th class="px-5 py-3">Unit</th>
                            <th class="px-5 py-3 text-right">Stock</th>
                            <th class="px-5 py-3 text-right">Purchase</th>
                            <th class="px-5 py-3 text-right">Sale</th>
                            <th class="px-5 py-3 text-right">Stock value</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($products as $product)
                            <tr class="hover:bg-stone-50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('products.show', $product) }}" class="font-medium text-stone-900 hover:text-emerald-700">{{ $product->name }}</a>
                                    <span class="block text-xs text-stone-400">{{ $product->sku ?: 'No SKU' }}</span>
                                </td>
                                <td class="px-5 py-3 text-stone-600">{{ $product->unit }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-stone-800">
                                    {{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }}
                                </td>
                                <td class="px-5 py-3 text-right text-stone-600"><x-money :value="$product->purchase_price" /></td>
                                <td class="px-5 py-3 text-right text-stone-600"><x-money :value="$product->sale_price" /></td>
                                <td class="px-5 py-3 text-right font-semibold text-stone-900"><x-money :value="$product->stockValue()" /></td>
                                <td class="px-5 py-3">
                                    @if ($product->isLowStock())
                                        <x-badge tone="rose">Low</x-badge>
                                    @else
                                        <x-badge tone="emerald">OK</x-badge>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-stone-300 bg-stone-50">
                            <td colspan="5" class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-stone-600">Total stock value</td>
                            <td class="px-5 py-3 text-right font-extrabold text-stone-900"><x-money :value="$summary['value']" /></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
