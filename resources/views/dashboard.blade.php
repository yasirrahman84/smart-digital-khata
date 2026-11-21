<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="subtitle">{{ now()->format('l, d M Y') }} · {{ auth()->user()->storeName() }}</x-slot>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-bold text-stone-900">Assalam-o-Alaikum, {{ explode(' ', auth()->user()->name)[0] }}</h2>
            <p class="text-sm text-stone-500">Here is how your shop is doing today.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('sales.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                <x-heroicon-o-plus class="h-5 w-5" />
                New sale
            </a>
            <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                <x-heroicon-o-user-plus class="h-5 w-5" />
                Add customer
            </a>
        </div>
    </div>

    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total receivable" :value="'Rs '.number_format($data['receivable'])" tone="rose" icon="book-open" hint="Money customers owe you" />
        <x-stat-card label="Today's sales" :value="'Rs '.number_format($data['todayTotal'])" tone="emerald" icon="shopping-cart" :hint="$data['todayCount'].' sales today'" />
        <x-stat-card label="Cash collected (month)" :value="'Rs '.number_format($data['cashCollected'])" tone="sky" icon="banknotes" :hint="'Cash sales + khata payments'" />
        <x-stat-card label="Low stock" :value="$data['lowStockCount']" tone="amber" icon="exclamation-triangle" hint="Items at or below threshold" />
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        {{-- Chart --}}
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-stone-900">Sales this week</h3>
                    <p class="text-xs text-stone-500">Cash vs udhaar, last 7 days</p>
                </div>
                <a href="{{ route('reports.sales') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">Sales report</a>
            </div>

            <div class="mt-4 h-64">
                <canvas id="salesChart" aria-label="Sales chart" role="img"></canvas>
            </div>
        </div>

        {{-- Top debtors --}}
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-stone-900">Top debtors</h3>
                <a href="{{ route('reports.receivables') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">All</a>
            </div>

            @if ($data['topDebtors']->isEmpty())
                <p class="mt-4 text-sm text-stone-500">Nobody owes you money right now.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-100">
                    @foreach ($data['topDebtors'] as $customer)
                        <li>
                            <a href="{{ route('customers.show', $customer) }}" class="flex items-center justify-between gap-2 py-2.5">
                                <span class="flex min-w-0 items-center gap-2.5">
                                    <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full bg-amber-100 text-[11px] font-bold text-amber-800">
                                        {{ $customer->initials() }}
                                    </span>
                                    <span class="truncate text-sm font-medium text-stone-800">{{ $customer->name }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-semibold text-rose-600"><x-money :value="$customer->balance" /></span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-5 grid gap-5 lg:grid-cols-2">
        {{-- Low stock --}}
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-stone-900">Running low</h3>
                <a href="{{ route('products.index', ['filter' => 'low']) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">Manage stock</a>
            </div>

            @if ($data['lowStockProducts']->isEmpty())
                <p class="mt-4 text-sm text-stone-500">Everything is above its threshold. Nice.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-100">
                    @foreach ($data['lowStockProducts'] as $product)
                        <li>
                            <a href="{{ route('products.show', $product) }}" class="flex items-center justify-between gap-2 py-2.5">
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium text-stone-800">{{ $product->name }}</span>
                                    <span class="text-xs text-stone-500">Alert at {{ rtrim(rtrim(number_format((float) $product->low_stock_threshold, 3), '0'), '.') }} {{ $product->unit }}</span>
                                </span>
                                <x-badge tone="rose">
                                    {{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }} left
                                </x-badge>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Activity --}}
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="font-semibold text-stone-900">Recent activity</h3>
                <a href="{{ route('khata.index') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">Khata book</a>
            </div>

            @if ($data['activity']->isEmpty())
                <p class="mt-4 text-sm text-stone-500">No activity yet. Record a sale or a khata entry to get started.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-100">
                    @foreach ($data['activity'] as $item)
                        <li>
                            <a href="{{ $item['url'] ?? '#' }}" class="flex items-center gap-3 py-2.5">
                                <span class="grid h-8 w-8 shrink-0 place-items-center rounded-full
                                    {{ $item['kind'] === 'payment' ? 'bg-emerald-100 text-emerald-700' : ($item['kind'] === 'udhaar' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700') }}">
                                    @if ($item['kind'] === 'payment')
                                        <x-heroicon-o-arrow-down-left class="h-4 w-4" />
                                    @elseif ($item['kind'] === 'udhaar')
                                        <x-heroicon-o-arrow-up-right class="h-4 w-4" />
                                    @else
                                        <x-heroicon-o-shopping-cart class="h-4 w-4" />
                                    @endif
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-medium text-stone-800">{{ $item['title'] }}</span>
                                    <span class="block text-xs text-stone-500">{{ $item['detail'] }} · {{ $item['at']->diffForHumans() }}</span>
                                </span>
                                <span class="shrink-0 text-sm font-semibold {{ $item['kind'] === 'payment' ? 'text-emerald-600' : 'text-stone-800' }}">
                                    {{ $item['kind'] === 'payment' ? '−' : '' }}<x-money :value="$item['amount']" />
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const canvas = document.getElementById('salesChart');

                if (!canvas || !window.Chart) {
                    return;
                }

                new window.Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: @js($data['chart']['labels']),
                        datasets: [
                            {
                                label: 'Cash',
                                data: @js($data['chart']['cash']),
                                borderColor: '#059669',
                                backgroundColor: 'rgba(5, 150, 105, 0.12)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                            },
                            {
                                label: 'Udhaar',
                                data: @js($data['chart']['udhaar']),
                                borderColor: '#d97706',
                                backgroundColor: 'rgba(217, 119, 6, 0.10)',
                                fill: true,
                                tension: 0.35,
                                pointRadius: 3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true } },
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: (value) => 'Rs ' + Number(value).toLocaleString('en-PK'),
                                },
                            },
                        },
                    },
                });
            });
        </script>
    @endpush
</x-app-layout>
