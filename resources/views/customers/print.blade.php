<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Statement · {{ $customer->name }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">
@php($shop = auth()->user())

<div class="mx-auto max-w-3xl px-4 py-8 print:max-w-none print:px-0 print:py-0">
    <div class="no-print mb-4 flex items-center justify-between">
        <a href="{{ route('customers.show', $customer) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">&larr; Back to khata</a>
        <button type="button" onclick="window.print()" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
            Print / Save as PDF
        </button>
    </div>

    <div class="print-card rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        <header class="flex flex-wrap items-start justify-between gap-4 border-b border-stone-200 pb-5">
            <div>
                <h1 class="text-xl font-extrabold text-stone-900">{{ $shop->storeName() }}</h1>
                @if ($shop->store_address)
                    <p class="text-sm text-stone-500">{{ $shop->store_address }}</p>
                @endif
                @if ($shop->store_phone)
                    <p class="text-sm text-stone-500">{{ $shop->store_phone }}</p>
                @endif
            </div>

            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Khata statement</p>
                <p class="text-sm text-stone-600">Issued {{ now()->format('d M Y') }}</p>
            </div>
        </header>

        <section class="mt-5 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Customer</p>
                <p class="text-lg font-bold text-stone-900">{{ $customer->name }}</p>
                <p class="text-sm text-stone-500">{{ $customer->phone ?: 'No phone number' }}</p>
                @if ($customer->address)
                    <p class="text-sm text-stone-500">{{ $customer->address }}</p>
                @endif
            </div>

            <div class="rounded-xl bg-stone-50 px-5 py-3 text-right">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Balance due</p>
                <p class="text-2xl font-extrabold {{ $balance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                    <x-money :value="$balance" />
                </p>
            </div>
        </section>

        <table class="mt-6 min-w-full divide-y divide-stone-200 text-sm">
            <thead class="text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="py-2">Date</th>
                    <th class="py-2">Description</th>
                    <th class="py-2 text-right">Udhaar</th>
                    <th class="py-2 text-right">Payment</th>
                    <th class="py-2 text-right">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                <tr>
                    <td class="py-2 text-stone-500">—</td>
                    <td class="py-2 font-medium text-stone-700">Opening balance</td>
                    <td class="py-2"></td>
                    <td class="py-2"></td>
                    <td class="py-2 text-right font-semibold"><x-money :value="$customer->opening_balance" /></td>
                </tr>

                @forelse ($entries as $entry)
                    <tr>
                        <td class="whitespace-nowrap py-2 text-stone-600">{{ $entry->entry_date->format('d M Y') }}</td>
                        <td class="py-2 text-stone-700">{{ $entry->description ?: ($entry->isUdhaar() ? 'Udhaar' : 'Payment') }}</td>
                        <td class="whitespace-nowrap py-2 text-right text-rose-600">
                            @if ($entry->isUdhaar())
                                <x-money :value="$entry->amount" />
                            @endif
                        </td>
                        <td class="whitespace-nowrap py-2 text-right text-emerald-600">
                            @if ($entry->isPayment())
                                <x-money :value="$entry->amount" />
                            @endif
                        </td>
                        <td class="whitespace-nowrap py-2 text-right font-semibold text-stone-900"><x-money :value="$entry->running_balance" /></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-stone-500">No entries recorded.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-stone-300">
                    <td colspan="4" class="py-3 text-right text-sm font-bold uppercase tracking-wide text-stone-600">Closing balance</td>
                    <td class="py-3 text-right text-base font-extrabold text-stone-900"><x-money :value="$balance" /></td>
                </tr>
            </tfoot>
        </table>

        <footer class="mt-8 border-t border-stone-200 pt-4 text-center text-xs text-stone-500">
            Generated by Smart Digital Khata · {{ now()->format('d M Y, g:i a') }}
        </footer>
    </div>
</div>
</body>
</html>
