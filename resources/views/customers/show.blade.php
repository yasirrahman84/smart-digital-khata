<x-app-layout>
    <x-slot name="title">{{ $customer->name }}</x-slot>
    <x-slot name="subtitle">Khata statement and account details</x-slot>

    <div class="space-y-5">
        {{-- Summary header --}}
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-emerald-100 text-lg font-bold text-emerald-800">
                        {{ $customer->initials() }}
                    </span>
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-stone-900">{{ $customer->name }}</h2>
                            @unless ($customer->is_active)
                                <x-badge tone="amber">Inactive</x-badge>
                            @endunless
                        </div>
                        <p class="mt-0.5 text-sm text-stone-500">
                            {{ $customer->phone ?: 'No phone number' }}
                            @if ($customer->address)
                                · {{ $customer->address }}
                            @endif
                        </p>
                        @if ($customer->notes)
                            <p class="mt-2 rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-600">{{ $customer->notes }}</p>
                        @endif
                    </div>
                </div>

                <div class="sm:text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Current balance</p>
                    <p class="mt-1 text-3xl font-extrabold {{ $balance > 0 ? 'text-rose-600' : ($balance < 0 ? 'text-emerald-600' : 'text-stone-900') }}">
                        <x-money :value="$balance" />
                    </p>
                    <p class="mt-1 text-xs text-stone-500">
                        @if ($balance > 0)
                            Customer owes you
                        @elseif ($balance < 0)
                            Customer paid in advance
                        @else
                            All settled
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-stone-100 bg-stone-50 px-5 py-3 sm:px-6">
                <a href="{{ route('customers.print', $customer) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm ring-1 ring-stone-200 hover:bg-stone-100">
                    <x-heroicon-o-printer class="h-4 w-4" />
                    Print statement
                </a>

                <a href="{{ route('customers.edit', $customer) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm ring-1 ring-stone-200 hover:bg-stone-100">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                    Edit
                </a>

                @if (Route::has('customers.remind'))
                    <form method="POST" action="{{ route('customers.remind', $customer) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700" @disabled(! $customer->phone || $balance <= 0)>
                            <x-heroicon-o-device-phone-mobile class="h-4 w-4" />
                            Send reminder
                        </button>
                    </form>
                @endif

                @if (Route::has('public.statement'))
                    <a href="{{ route('public.statement', $customer->public_token) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-200 hover:bg-emerald-50">
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                        Customer link
                    </a>
                @endif
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            {{-- New entry --}}
            <div class="lg:col-span-1">
                <form method="POST" action="{{ route('customers.entries.store', $customer) }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    @csrf

                    <h3 class="font-semibold text-stone-900">Record entry</h3>
                    <p class="mt-1 text-xs text-stone-500">Add udhaar or a payment received.</p>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="udhaar" class="peer sr-only" @checked(old('type') === 'udhaar')>
                            <span class="block rounded-xl border border-stone-200 px-3 py-2 text-center text-sm font-semibold text-stone-600 transition peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700">
                                Udhaar
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="type" value="payment" class="peer sr-only" @checked(old('type', 'payment') === 'payment')>
                            <span class="block rounded-xl border border-stone-200 px-3 py-2 text-center text-sm font-semibold text-stone-600 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                Payment
                            </span>
                        </label>
                    </div>
                    <x-input-error :messages="$errors->get('type')" />

                    <div class="mt-3">
                        <x-input-label for="amount" value="Amount (Rs)" />
                        <x-text-input id="amount" name="amount" type="number" step="0.01" min="0.01" class="mt-1" :value="old('amount')" placeholder="0.00" required />
                        <x-input-error :messages="$errors->get('amount')" />
                    </div>

                    <div class="mt-3">
                        <x-input-label for="entry_date" value="Date" />
                        <x-text-input id="entry_date" name="entry_date" type="date" class="mt-1" :value="old('entry_date', now()->toDateString())" max="{{ now()->toDateString() }}" required />
                        <x-input-error :messages="$errors->get('entry_date')" />
                    </div>

                    <div class="mt-3">
                        <x-input-label for="description" value="Description (optional)" />
                        <x-text-input id="description" name="description" type="text" class="mt-1" :value="old('description')" placeholder="e.g. Groceries on credit" />
                        <x-input-error :messages="$errors->get('description')" />
                    </div>

                    <x-primary-button class="mt-4 w-full">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        Add to khata
                    </x-primary-button>
                </form>
            </div>

            {{-- Statement --}}
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 px-5 py-4">
                        <div>
                            <h3 class="font-semibold text-stone-900">Khata statement</h3>
                            <p class="text-xs text-stone-500">{{ $entries->total() }} entries</p>
                        </div>

                        <div class="flex gap-3 text-xs">
                            <span class="text-stone-500">Udhaar <strong class="text-rose-600"><x-money :value="$totals['udhaar']" /></strong></span>
                            <span class="text-stone-500">Paid <strong class="text-emerald-600"><x-money :value="$totals['payment']" /></strong></span>
                        </div>
                    </div>

                    @if ($entries->isEmpty())
                        <div class="px-5 py-12 text-center">
                            <span class="mx-auto grid h-11 w-11 place-items-center rounded-full bg-stone-100 text-stone-400">
                                <x-heroicon-o-book-open class="h-5 w-5" />
                            </span>
                            <p class="mt-3 font-medium text-stone-700">No entries yet</p>
                            <p class="mt-1 text-sm text-stone-500">Use the form to record the first udhaar or payment.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-stone-200 text-sm">
                                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                                    <tr>
                                        <th class="px-5 py-3">Date</th>
                                        <th class="px-5 py-3">Description</th>
                                        <th class="px-5 py-3 text-right">Udhaar</th>
                                        <th class="px-5 py-3 text-right">Payment</th>
                                        <th class="px-5 py-3 text-right">Balance</th>
                                        <th class="px-2 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    @foreach ($entries as $entry)
                                        <tr class="hover:bg-stone-50">
                                            <td class="whitespace-nowrap px-5 py-3 text-stone-600">{{ $entry->entry_date->format('d M Y') }}</td>
                                            <td class="px-5 py-3">
                                                <span class="block text-stone-800">{{ $entry->description ?: ($entry->isUdhaar() ? 'Udhaar' : 'Payment') }}</span>
                                                @if ($entry->sale)
                                                    <span class="text-xs text-stone-400">Sale {{ $entry->sale->invoice_no }}</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-rose-600">
                                                @if ($entry->isUdhaar())
                                                    <x-money :value="$entry->amount" />
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right font-medium text-emerald-600">
                                                @if ($entry->isPayment())
                                                    <x-money :value="$entry->amount" />
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-stone-900">
                                                <x-money :value="$entry->running_balance" />
                                            </td>
                                            <td class="px-2 py-3 text-right">
                                                @if ($entry->sale_id === null)
                                                    <form method="POST" action="{{ route('customers.entries.destroy', [$customer, $entry]) }}" onsubmit="return confirm('Delete this entry?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="rounded-lg p-1.5 text-stone-400 hover:bg-rose-50 hover:text-rose-600" title="Delete entry">
                                                            <x-heroicon-o-trash class="h-4 w-4" />
                                                        </button>
                                                    </form>
                                                @endif
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
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            {{-- Recent sales --}}
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-stone-900">Recent sales</h3>
                    @if (Route::has('sales.index'))
                        <a href="{{ route('sales.index', ['customer' => $customer->id]) }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900">View all</a>
                    @endif
                </div>

                @if ($recentSales->isEmpty())
                    <p class="mt-3 text-sm text-stone-500">No sales recorded for this customer yet.</p>
                @else
                    <ul class="mt-3 divide-y divide-stone-100">
                        @foreach ($recentSales as $sale)
                            <li class="flex items-center justify-between gap-3 py-2.5">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-medium text-stone-800">{{ $sale->invoice_no }}</p>
                                    <p class="text-xs text-stone-500">{{ $sale->sale_date->format('d M Y, g:i a') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold text-stone-900"><x-money :value="$sale->total" /></p>
                                    <x-badge :tone="$sale->isUdhaar() ? 'amber' : 'emerald'">{{ $sale->isUdhaar() ? 'Udhaar' : 'Cash' }}</x-badge>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Reminders --}}
            <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                <h3 class="font-semibold text-stone-900">Reminders sent</h3>

                @if ($recentMessages->isEmpty())
                    <p class="mt-3 text-sm text-stone-500">No reminders sent to this customer yet.</p>
                @else
                    <ul class="mt-3 space-y-3">
                        @foreach ($recentMessages as $message)
                            <li class="rounded-xl bg-stone-50 p-3">
                                <div class="flex items-center justify-between gap-2">
                                    <x-badge :tone="$message->status === 'failed' ? 'rose' : 'emerald'">{{ ucfirst($message->status) }}</x-badge>
                                    <span class="text-xs text-stone-400">{{ $message->created_at->format('d M Y') }}</span>
                                </div>
                                <p class="mt-2 line-clamp-3 text-xs leading-relaxed text-stone-600">{{ $message->body }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
