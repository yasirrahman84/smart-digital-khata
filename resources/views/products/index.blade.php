<x-app-layout>
    <x-slot name="title">Stock</x-slot>
    <x-slot name="subtitle">{{ $summary['items'] }} active items · Rs {{ number_format($summary['value']) }} stock value</x-slot>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Active products" :value="$summary['items']" tone="sky" icon="cube" />
        <x-stat-card label="Low stock" :value="$summary['low']" tone="rose" icon="exclamation-triangle" hint="At or below threshold" />
        <x-stat-card label="Stock value" :value="'Rs '.number_format($summary['value'])" tone="emerald" icon="banknotes" hint="At purchase price" />
    </div>

    <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-1 flex-col gap-2 sm:flex-row">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-stone-500">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                </span>
                <x-text-input name="q" type="search" class="pl-10" :value="$search" placeholder="Search by name or SKU…" />
            </div>

            <x-select name="filter" class="sm:w-44" onchange="this.form.submit()">
                <option value="all" @selected($filter === 'all')>Active products</option>
                <option value="low" @selected($filter === 'low')>Low stock only</option>
                <option value="archived" @selected($filter === 'archived')>Archived</option>
            </x-select>

            <x-select name="sort" class="sm:w-44" onchange="this.form.submit()">
                <option value="name" @selected($sort === 'name')>Sort: Name</option>
                <option value="stock" @selected($sort === 'stock')>Sort: Stock (low first)</option>
                <option value="value" @selected($sort === 'value')>Sort: Stock value</option>
            </x-select>
        </form>

        <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            <x-heroicon-o-plus class="h-5 w-5" />
            Add product
        </a>
    </div>

    @if ($products->isEmpty())
        <div class="mt-6">
            <x-empty-state
                icon="cube"
                :title="$search !== '' ? 'No products match your search' : ($filter === 'low' ? 'Nothing is running low' : 'No products yet')"
                :description="$search !== '' ? 'Try a different name or SKU.' : ($filter === 'low' ? 'Every active item is above its threshold.' : 'Add your first product to start tracking stock.')"
            >
                <x-slot name="actions">
                    <a href="{{ route('products.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        Add a product
                    </a>
                </x-slot>
            </x-empty-state>
        </div>
    @else
        {{-- Desktop table --}}
        <div class="mt-6 hidden overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm sm:block">
            <table class="min-w-full divide-y divide-stone-200 text-sm">
                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-5 py-3 text-right">Stock</th>
                        <th class="px-5 py-3 text-right">Purchase</th>
                        <th class="px-5 py-3 text-right">Sale</th>
                        <th class="px-5 py-3 text-right">Stock value</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($products as $product)
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('products.show', $product) }}" class="block">
                                    <span class="block font-medium text-stone-900">{{ $product->name }}</span>
                                    <span class="text-xs text-stone-500">{{ $product->sku ?: 'No SKU' }} · per {{ $product->unit }}</span>
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                @if (! $product->is_active)
                                    <x-badge tone="stone">Archived</x-badge>
                                @elseif ($product->isLowStock())
                                    <x-badge tone="rose">{{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }} {{ $product->unit }}</x-badge>
                                @else
                                    <span class="font-semibold text-stone-800">{{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }}</span>
                                    <span class="text-xs text-stone-500">{{ $product->unit }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right text-stone-600"><x-money :value="$product->purchase_price" /></td>
                            <td class="px-5 py-3.5 text-right font-medium text-stone-800"><x-money :value="$product->sale_price" /></td>
                            <td class="px-5 py-3.5 text-right text-stone-600"><x-money :value="$product->stockValue()" /></td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('products.show', $product) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                                    Manage
                                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="mt-6 space-y-3 sm:hidden">
            @foreach ($products as $product)
                <a href="{{ route('products.show', $product) }}" class="block rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate font-semibold text-stone-900">{{ $product->name }}</p>
                            <p class="text-xs text-stone-500">{{ $product->sku ?: 'No SKU' }} · <x-money :value="$product->sale_price" /> / {{ $product->unit }}</p>
                        </div>
                        @if (! $product->is_active)
                            <x-badge tone="stone">Archived</x-badge>
                        @elseif ($product->isLowStock())
                            <x-badge tone="rose">{{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }} left</x-badge>
                        @else
                            <x-badge tone="emerald">{{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }} in stock</x-badge>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">
            {{ $products->links() }}
        </div>
    @endif
</x-app-layout>
