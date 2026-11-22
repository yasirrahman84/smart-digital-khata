<x-app-layout>
    <x-slot name="title">Sales</x-slot>
    <x-slot name="subtitle">Invoices, cash and udhaar sales</x-slot>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Sales" :value="$summary['count']" tone="sky" icon="receipt-percent" />
        <x-stat-card label="Revenue" :value="'Rs '.number_format($summary['revenue'])" tone="emerald" icon="banknotes" />
        <x-stat-card label="Cash" :value="'Rs '.number_format($summary['cash'])" tone="stone" icon="wallet" />
        <x-stat-card label="Udhaar" :value="'Rs '.number_format($summary['udhaar'])" tone="amber" icon="book-open" />
    </div>

    <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('sales.index') }}" class="flex flex-1 flex-wrap items-end gap-2">
            <div>
                <x-input-label for="from" value="From" class="text-xs" />
                <x-text-input id="from" name="from" type="date" class="mt-1" :value="$from" />
            </div>
            <div>
                <x-input-label for="to" value="To" class="text-xs" />
                <x-text-input id="to" name="to" type="date" class="mt-1" :value="$to" />
            </div>
            <div>
                <x-input-label for="payment" value="Payment" class="text-xs" />
                <x-select id="payment" name="payment" class="mt-1">
                    <option value="">All</option>
                    <option value="cash" @selected($method === 'cash')>Cash</option>
                    <option value="udhaar" @selected($method === 'udhaar')>Udhaar</option>
                </x-select>
            </div>
            <div>
                <x-input-label for="status" value="Status" class="text-xs" />
                <x-select id="status" name="status" class="mt-1">
                    <option value="all" @selected($status === 'all')>All</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="voided" @selected($status === 'voided')>Voided</option>
                </x-select>
            </div>
            <div>
                <x-input-label for="customer" value="Customer" class="text-xs" />
                <x-select id="customer" name="customer" class="mt-1">
                    <option value="">All</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) $customerId === (string) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <x-secondary-button type="submit">Filter</x-secondary-button>

            @if ($from || $to || $method || $customerId || $status !== 'all')
                <a href="{{ route('sales.index') }}" class="px-2 py-2.5 text-sm font-semibold text-stone-500 hover:text-stone-800">Reset</a>
            @endif
        </form>

        <a href="{{ route('sales.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            <x-heroicon-o-plus class="h-5 w-5" />
            New sale
        </a>
    </div>

    <div class="mt-5 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        @if ($sales->isEmpty())
            <x-empty-state
                icon="shopping-cart"
                title="No sales found"
                description="Record your first sale, or adjust the filters above."
            >
                <x-slot name="actions">
                    <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        New sale
                    </a>
                </x-slot>
            </x-empty-state>
        @else
            <div class="hidden overflow-x-auto sm:block">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Payment</th>
                            <th class="px-5 py-3 text-right">Items</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($sales as $sale)
                            <tr class="{{ $sale->isVoided() ? 'bg-stone-50 text-stone-500' : 'hover:bg-stone-50' }}">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('sales.show', $sale) }}" class="font-semibold {{ $sale->isVoided() ? 'text-stone-500 line-through' : 'text-emerald-700 hover:text-emerald-900' }}">
                                        {{ $sale->invoice_no }}
                                    </a>
                                    @if ($sale->isVoided())
                                        <x-badge tone="rose" class="ml-1">Void</x-badge>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-stone-600">{{ $sale->sale_date->format('d M Y, g:i a') }}</td>
                                <td class="px-5 py-3.5 text-stone-600">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                <td class="px-5 py-3.5">
                                    <x-badge :tone="$sale->isUdhaar() ? 'amber' : 'emerald'">{{ $sale->isUdhaar() ? 'Udhaar' : 'Cash' }}</x-badge>
                                </td>
                                <td class="px-5 py-3.5 text-right text-stone-600">{{ $sale->items_count }}</td>
                                <td class="px-5 py-3.5 text-right font-semibold {{ $sale->isVoided() ? 'text-stone-500 line-through' : 'text-stone-900' }}">
                                    <x-money :value="$sale->total" />
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('sales.show', $sale) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                                        Receipt
                                        <x-heroicon-o-chevron-right class="h-4 w-4" />
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="divide-y divide-stone-100 sm:hidden">
                @foreach ($sales as $sale)
                    <a href="{{ route('sales.show', $sale) }}" class="block px-4 py-3 {{ $sale->isVoided() ? 'opacity-60' : '' }}">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-stone-900">{{ $sale->invoice_no }}</p>
                                <p class="text-xs text-stone-500">{{ $sale->sale_date->format('d M Y, g:i a') }} · {{ $sale->customer?->name ?? 'Walk-in' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold text-stone-900"><x-money :value="$sale->total" /></p>
                                <x-badge :tone="$sale->isVoided() ? 'rose' : ($sale->isUdhaar() ? 'amber' : 'emerald')">
                                    {{ $sale->isVoided() ? 'Void' : ($sale->isUdhaar() ? 'Udhaar' : 'Cash') }}
                                </x-badge>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="border-t border-stone-100 px-5 py-3">
                {{ $sales->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
