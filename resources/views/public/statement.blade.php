<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#022c22">

    <title>Statement · {{ $customer->name }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">
<div class="mx-auto max-w-2xl px-4 py-6 sm:py-10">
    {{-- Shop header --}}
    <header class="rounded-2xl bg-emerald-950 px-5 py-5 text-white shadow-sm">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-emerald-500 text-lg font-bold">ک</span>
            <div class="min-w-0">
                <h1 class="truncate text-lg font-bold">{{ $shop->storeName() }}</h1>
                @if ($shop->store_address)
                    <p class="truncate text-xs text-emerald-200">{{ $shop->store_address }}</p>
                @endif
                @if ($shop->store_phone)
                    <p class="text-xs text-emerald-200">{{ $shop->store_phone }}</p>
                @endif
            </div>
        </div>
    </header>

    <main class="mt-4 space-y-4">
        {{-- Balance --}}
        <section class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Statement for</p>
                    <p class="truncate text-lg font-bold text-stone-900">{{ $customer->name }}</p>
                    <p class="text-xs text-stone-500">Updated {{ now()->format('d M Y, g:i a') }}</p>
                </div>

                <div class="text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Balance</p>
                    <p class="text-2xl font-extrabold {{ $balance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                        <x-money :value="$balance" />
                    </p>
                    <p class="text-xs text-stone-500">
                        @if ($balance > 0)
                            Amount due
                        @elseif ($balance < 0)
                            Paid in advance
                        @else
                            All settled
                        @endif
                    </p>
                </div>
            </div>
        </section>

        {{-- Entries --}}
        <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-3">
                <h2 class="text-sm font-semibold text-stone-900">Recent entries</h2>
                <p class="text-xs text-stone-500">Last {{ $entries->count() }} of {{ $customer->ledgerEntries()->count() }} entries</p>
            </div>

            @if ($entries->isEmpty())
                <p class="px-5 py-10 text-center text-sm text-stone-500">No khata entries yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-stone-200 text-sm">
                        <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                            <tr>
                                <th class="px-4 py-2.5">Date</th>
                                <th class="px-4 py-2.5">Description</th>
                                <th class="px-4 py-2.5 text-right">Udhaar</th>
                                <th class="px-4 py-2.5 text-right">Paid</th>
                                <th class="px-4 py-2.5 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            @foreach ($entries as $entry)
                                <tr>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-stone-600">{{ $entry->entry_date->format('d M Y') }}</td>
                                    <td class="px-4 py-2.5 text-stone-700">{{ $entry->description ?: ($entry->isUdhaar() ? 'Udhaar' : 'Payment') }}</td>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-right text-rose-600">
                                        @if ($entry->isUdhaar())
                                            <x-money :value="$entry->amount" />
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-right text-emerald-600">
                                        @if ($entry->isPayment())
                                            <x-money :value="$entry->amount" />
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-2.5 text-right font-semibold text-stone-900">
                                        <x-money :value="$entry->running_balance" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <p class="px-1 text-xs leading-relaxed text-stone-500">
            This statement is generated automatically from the shop's khata. If something looks wrong,
            please contact {{ $shop->storeName() }}@if ($shop->store_phone) on {{ $shop->store_phone }}@endif.
        </p>

        <div class="no-print flex justify-center gap-2 pt-2">
            <button type="button" onclick="window.print()" class="rounded-xl border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                Print / Save as PDF
            </button>
            <a href="tel:{{ preg_replace('/[^0-9+]/', '', (string) $shop->store_phone) }}" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Call shop
            </a>
        </div>

        <p class="pb-4 text-center text-[11px] text-stone-400">Powered by Smart Digital Khata</p>
    </main>
</div>
</body>
</html>
