<x-app-layout>
    <x-slot name="title">Khata book</x-slot>
    <x-slot name="subtitle">Every udhaar and payment across all customers</x-slot>

    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Total udhaar" :value="'Rs '.number_format($totals['udhaar'])" tone="rose" icon="arrow-trending-up" />
        <x-stat-card label="Total payments" :value="'Rs '.number_format($totals['payment'])" tone="emerald" icon="arrow-trending-down" />
        <x-stat-card label="Net outstanding" :value="'Rs '.number_format(max(0, $totals['udhaar'] - $totals['payment']))" tone="sky" icon="scale" />
    </div>

    <form method="GET" action="{{ route('khata.index') }}" class="mt-5 rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div>
                <x-input-label for="from" value="From" />
                <x-text-input id="from" name="from" type="date" class="mt-1" :value="$from" />
            </div>
            <div>
                <x-input-label for="to" value="To" />
                <x-text-input id="to" name="to" type="date" class="mt-1" :value="$to" />
            </div>
            <div>
                <x-input-label for="type" value="Type" />
                <x-select id="type" name="type" class="mt-1">
                    <option value="">All entries</option>
                    <option value="udhaar" @selected($type === 'udhaar')>Udhaar only</option>
                    <option value="payment" @selected($type === 'payment')>Payments only</option>
                </x-select>
            </div>
            <div>
                <x-input-label for="customer" value="Customer" />
                <x-select id="customer" name="customer" class="mt-1">
                    <option value="">All customers</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) $customerId === (string) $customer->id)>{{ $customer->name }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="flex items-end gap-2">
                <x-primary-button class="flex-1">
                    <x-heroicon-o-funnel class="h-4 w-4" />
                    Filter
                </x-primary-button>
                @if ($from || $to || $type || $customerId)
                    <a href="{{ route('khata.index') }}" class="rounded-xl px-3 py-2.5 text-sm font-semibold text-stone-500 hover:bg-stone-100">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="mt-5 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
            <h2 class="font-semibold text-stone-900">Entries</h2>
            <span class="text-xs text-stone-500">{{ $entries->total() }} results</span>
        </div>

        @if ($entries->isEmpty())
            <x-empty-state
                icon="book-open"
                title="No khata entries found"
                description="Adjust the filters above, or record an entry from a customer's khata page."
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-stone-200 text-sm">
                    <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Description</th>
                            <th class="px-5 py-3">Type</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($entries as $entry)
                            <tr class="hover:bg-stone-50">
                                <td class="whitespace-nowrap px-5 py-3 text-stone-600">{{ $entry->entry_date->format('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    @if ($entry->customer)
                                        <a href="{{ route('customers.show', $entry->customer) }}" class="font-medium text-emerald-700 hover:text-emerald-900">{{ $entry->customer->name }}</a>
                                    @else
                                        <span class="text-stone-500">Deleted customer</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-stone-600">{{ $entry->description ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <x-badge :tone="$entry->isUdhaar() ? 'rose' : 'emerald'">{{ $entry->isUdhaar() ? 'Udhaar' : 'Payment' }}</x-badge>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold {{ $entry->isUdhaar() ? 'text-rose-600' : 'text-emerald-600' }}">
                                    {{ $entry->isUdhaar() ? '+' : '−' }}<x-money :value="$entry->amount" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-stone-100 px-5 py-3">
                {{ $entries->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
