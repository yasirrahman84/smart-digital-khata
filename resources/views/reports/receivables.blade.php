<x-app-layout>
    <x-slot name="title">Receivables</x-slot>
    <x-slot name="subtitle">Who owes you money, and for how long</x-slot>

    @include('reports._tabs', ['current' => 'receivables'])

    @php
        $buckets = $rows->countBy('bucket');
        $over60 = $rows->where('bucket', '60+')->sum('balance');
    @endphp

    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-stat-card label="Total receivable" :value="'Rs '.number_format($total)" tone="rose" icon="book-open" />
        <x-stat-card label="Customers owing" :value="$rows->count()" tone="sky" icon="users" />
        <x-stat-card label="Older than 60 days" :value="'Rs '.number_format($over60)" tone="amber" icon="clock" />
        <x-stat-card label="Oldest debt" :value="$rows->max('age_days') ? $rows->max('age_days').' days' : '—'" tone="stone" icon="calendar-days" />
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-2">
        <div class="flex flex-wrap gap-2 text-xs">
            <x-badge tone="emerald">0-30 days: {{ $buckets['0-30'] ?? 0 }}</x-badge>
            <x-badge tone="amber">31-60 days: {{ $buckets['31-60'] ?? 0 }}</x-badge>
            <x-badge tone="rose">60+ days: {{ $buckets['60+'] ?? 0 }}</x-badge>
        </div>

        <div class="no-print flex gap-2">
            <a href="{{ route('reports.receivables.csv') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-stone-300 bg-white px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                Export CSV
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                <x-heroicon-o-printer class="h-4 w-4" />
                Print
            </button>
        </div>
    </div>

    <div class="mt-4 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        @if ($rows->isEmpty())
            <x-empty-state icon="check-badge" title="Nobody owes you money" description="Every khata is settled. That is a good day." />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3 text-right">Balance</th>
                            <th class="px-5 py-3">Oldest unpaid</th>
                            <th class="px-5 py-3 text-right">Age</th>
                            <th class="px-5 py-3">Bucket</th>
                            <th class="no-print px-5 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($rows as $row)
                            <tr class="hover:bg-stone-50">
                                <td class="px-5 py-3 font-medium text-stone-900">
                                    <a href="{{ route('customers.show', $row['customer']) }}" class="hover:text-emerald-700">{{ $row['customer']->name }}</a>
                                </td>
                                <td class="px-5 py-3 text-stone-600">{{ $row['customer']->phone ?: '—' }}</td>
                                <td class="px-5 py-3 text-right font-semibold text-rose-600"><x-money :value="$row['balance']" /></td>
                                <td class="whitespace-nowrap px-5 py-3 text-stone-600">{{ $row['oldest_date']?->format('d M Y') ?? '—' }}</td>
                                <td class="px-5 py-3 text-right text-stone-600">{{ $row['age_days'] }} d</td>
                                <td class="px-5 py-3">
                                    <x-badge :tone="$row['bucket'] === '60+' ? 'rose' : ($row['bucket'] === '31-60' ? 'amber' : 'emerald')">{{ $row['bucket'] }}</x-badge>
                                </td>
                                <td class="no-print px-5 py-3 text-right">
                                    <a href="{{ route('customers.show', $row['customer']) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">View khata</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-stone-300 bg-stone-50">
                            <td colspan="2" class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wide text-stone-600">Total</td>
                            <td class="px-5 py-3 text-right font-extrabold text-stone-900"><x-money :value="$total" /></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    <p class="mt-3 text-xs text-stone-500">
        Age is calculated FIFO: payments are applied to the oldest udhaar first. Opening balances count as the oldest debt.
    </p>
</x-app-layout>
