<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $sale->invoice_no }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css'])
</head>
<body class="bg-stone-100 font-sans text-stone-800 antialiased">
@php($shop = auth()->user())

<div class="mx-auto max-w-2xl px-4 py-8 print:max-w-none print:px-0 print:py-0">
    <div class="no-print mb-4 flex flex-wrap items-center justify-between gap-2">
        <a href="{{ route('sales.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">&larr; Back to sales</a>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Print receipt
            </button>

            @can('void', $sale)
                <form method="POST" action="{{ route('sales.void', $sale) }}" onsubmit="return confirm('Void this sale? Stock will be restored and the khata entry reversed.');">
                    @csrf
                    <button type="submit" class="rounded-xl border border-rose-300 bg-white px-4 py-2 text-sm font-semibold text-rose-600 hover:bg-rose-50">
                        Void sale
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <div class="print-card relative overflow-hidden rounded-2xl border border-stone-200 bg-white p-8 shadow-sm">
        @if ($sale->isVoided())
            <div class="pointer-events-none absolute inset-0 grid place-items-center">
                <span class="rotate-[-20deg] rounded-xl border-4 border-rose-400/70 px-8 py-3 text-4xl font-extrabold uppercase tracking-widest text-rose-400/70">
                    Void
                </span>
            </div>
        @endif

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
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Sale receipt</p>
                <p class="text-sm font-bold text-stone-900">{{ $sale->invoice_no }}</p>
                <p class="text-sm text-stone-500">{{ $sale->sale_date->format('d M Y, g:i a') }}</p>
            </div>
        </header>

        <section class="mt-5 flex flex-wrap items-start justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Customer</p>
                @if ($sale->customer)
                    <a href="{{ route('customers.show', $sale->customer) }}" class="font-bold text-stone-900 hover:text-emerald-700">{{ $sale->customer->name }}</a>
                    <p class="text-sm text-stone-500">{{ $sale->customer->phone ?: 'No phone number' }}</p>
                @else
                    <p class="font-bold text-stone-900">Walk-in customer</p>
                @endif
            </div>

            <div class="text-right">
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">Payment</p>
                <x-badge :tone="$sale->isUdhaar() ? 'amber' : 'emerald'" class="mt-1 text-sm">
                    {{ $sale->isUdhaar() ? 'Udhaar' : 'Cash' }}
                </x-badge>
            </div>
        </section>

        <table class="mt-6 min-w-full divide-y divide-stone-200 text-sm">
            <thead class="text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="py-2">Item</th>
                    <th class="py-2 text-right">Qty</th>
                    <th class="py-2 text-right">Price</th>
                    <th class="py-2 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach ($sale->items as $item)
                    <tr>
                        <td class="py-2.5">
                            <span class="font-medium text-stone-800">{{ $item->product_name }}</span>
                        </td>
                        <td class="whitespace-nowrap py-2.5 text-right text-stone-600">
                            {{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }}
                        </td>
                        <td class="whitespace-nowrap py-2.5 text-right text-stone-600"><x-money :value="$item->unit_price" /></td>
                        <td class="whitespace-nowrap py-2.5 text-right font-semibold text-stone-900"><x-money :value="$item->line_total" /></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-5 ml-auto max-w-xs space-y-1.5 text-sm">
            <div class="flex items-center justify-between">
                <span class="text-stone-500">Subtotal</span>
                <span class="text-stone-800"><x-money :value="$sale->subtotal" /></span>
            </div>

            @if ((float) $sale->discount > 0)
                <div class="flex items-center justify-between">
                    <span class="text-stone-500">Discount</span>
                    <span class="text-rose-600">−<x-money :value="$sale->discount" /></span>
                </div>
            @endif

            <div class="flex items-center justify-between border-t border-stone-200 pt-2">
                <span class="font-bold text-stone-900">Total</span>
                <span class="text-lg font-extrabold text-stone-900"><x-money :value="$sale->total" /></span>
            </div>
        </div>

        @if ($sale->notes)
            <p class="mt-4 rounded-xl bg-stone-50 px-4 py-3 text-sm text-stone-600">
                <span class="font-semibold text-stone-700">Note:</span> {{ $sale->notes }}
            </p>
        @endif

        @if ($sale->isUdhaar() && $sale->customer)
            <p class="mt-4 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-900">
                This sale was added to {{ $sale->customer->name }}'s khata.
                @unless ($sale->isVoided())
                    <a href="{{ route('customers.show', $sale->customer) }}" class="font-semibold underline">View khata</a>
                @endunless
            </p>
        @endif

        @if ($sale->isVoided())
            <p class="mt-4 rounded-xl bg-rose-50 px-4 py-3 text-sm text-rose-800">
                Voided {{ $sale->voided_at->format('d M Y, g:i a') }}. Stock was restored and the khata entry reversed.
            </p>
        @endif

        <footer class="mt-8 border-t border-stone-200 pt-4 text-center text-xs text-stone-500">
            Thank you for shopping with {{ $shop->storeName() }}.
        </footer>
    </div>
</div>
</body>
</html>
