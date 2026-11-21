<x-app-layout>
    <x-slot name="title">Sales report</x-slot>
    <x-slot name="subtitle">{{ \Illuminate\Support\Carbon::parse($from)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($to)->format('d M Y') }}</x-slot>

    @include('reports._tabs', ['current' => 'sales'])

    <form method="GET" action="{{ route('reports.sales') }}" class="no-print mt-5 flex flex-wrap items-end gap-2 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
        <div>
            <x-input-label for="from" value="From" class="text-xs" />
            <x-text-input id="from" name="from" type="date" class="mt-1" :value="$from" />
        </div>
        <div>
            <x-input-label for="to" value="To" class="text-xs" />
            <x-text-input id="to" name="to" type="date" class="mt-1" :value="$to" />
        </div>
        <x-primary-button>Apply</x-primary-button>

        <a href="{{ route('reports.sales.csv', ['from' => $from, 'to' => $to]) }}" class="inline-flex items-center gap-1.5 rounded-xl border border-stone-300 bg-white px-3 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
            <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
            Export CSV
        </a>
        <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            <x-heroicon-o-printer class="h-4 w-4" />
            Print
        </button>
    </form>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <x-stat-card label="Sales" :value="$summary['count']" tone="sky" icon="receipt-percent" />
        <x-stat-card label="Revenue" :value="'Rs '.number_format($summary['revenue'])" tone="emerald" icon="banknotes" />
        <x-stat-card label="Average sale" :value="'Rs '.number_format($summary['average'])" tone="stone" icon="chart-bar" />
        <x-stat-card label="Cash" :value="'Rs '.number_format($summary['cash'])" tone="emerald" icon="wallet" />
        <x-stat-card label="Udhaar" :value="'Rs '.number_format($summary['udhaar'])" tone="amber" icon="book-open" />
        <x-stat-card label="Discounts given" :value="'Rs '.number_format($summary['discount'])" tone="rose" icon="tag" />
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        {{-- Daily --}}
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4">
                <h3 class="font-semibold text-stone-900">Day by day</h3>
            </div>

            @if ($daily->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-stone-500">No sales in this period.</p>
            @else
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3 text-right">Sales</th>
                            <th class="px-5 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($daily as $day)
                            <tr>
                                <td class="px-5 py-2.5 text-stone-700">{{ \Illuminate\Support\Carbon::parse($day->day)->format('d M Y') }}</td>
                                <td class="px-5 py-2.5 text-right text-stone-600">{{ $day->sale_count }}</td>
                                <td class="px-5 py-2.5 text-right font-semibold text-stone-900"><x-money :value="$day->revenue" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- Top products --}}
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4">
                <h3 class="font-semibold text-stone-900">Top products</h3>
            </div>

            @if ($topProducts->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-stone-500">No products sold in this period.</p>
            @else
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-3">Product</th>
                            <th class="px-5 py-3 text-right">Qty</th>
                            <th class="px-5 py-3 text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($topProducts as $product)
                            <tr>
                                <td class="px-5 py-2.5 font-medium text-stone-800">{{ $product->product_name }}</td>
                                <td class="px-5 py-2.5 text-right text-stone-600">{{ rtrim(rtrim(number_format((float) $product->quantity, 3), '0'), '.') }}</td>
                                <td class="px-5 py-2.5 text-right font-semibold text-stone-900"><x-money :value="$product->revenue" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-app-layout>
