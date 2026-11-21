<x-app-layout>
    <x-slot name="title">{{ $product->name }}</x-slot>
    <x-slot name="subtitle">Stock details and movement history</x-slot>

    <div class="space-y-5">
        {{-- Header --}}
        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
            <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-start sm:justify-between sm:p-6">
                <div class="flex items-start gap-4">
                    <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-sky-100 text-sky-700">
                        <x-heroicon-o-cube class="h-7 w-7" />
                    </span>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-stone-900">{{ $product->name }}</h2>
                            @unless ($product->is_active)
                                <x-badge tone="stone">Archived</x-badge>
                            @endunless
                            @if ($product->isLowStock() && $product->is_active)
                                <x-badge tone="rose">Low stock</x-badge>
                            @endif
                        </div>
                        <p class="mt-0.5 text-sm text-stone-500">
                            {{ $product->sku ?: 'No SKU' }} · sold per {{ $product->unit }}
                        </p>
                        <p class="mt-2 text-sm text-stone-600">
                            Purchase <strong><x-money :value="$product->purchase_price" /></strong>
                            · Sale <strong><x-money :value="$product->sale_price" /></strong>
                            @php($margin = (float) $product->sale_price - (float) $product->purchase_price)
                            · Margin <strong class="{{ $margin >= 0 ? 'text-emerald-700' : 'text-rose-600' }}"><x-money :value="$margin" /></strong>
                        </p>
                    </div>
                </div>

                <div class="rounded-xl bg-stone-50 px-5 py-3 text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">In stock</p>
                    <p class="text-2xl font-extrabold {{ $product->isLowStock() ? 'text-rose-600' : 'text-stone-900' }}">
                        {{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }}
                        <span class="text-sm font-semibold text-stone-500">{{ $product->unit }}</span>
                    </p>
                    <p class="text-xs text-stone-500">Value <x-money :value="$product->stockValue()" /></p>
                </div>
            </div>

            <div class="flex flex-wrap gap-2 border-t border-stone-100 bg-stone-50 px-5 py-3 sm:px-6">
                <a href="{{ route('products.edit', $product) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-stone-700 shadow-sm ring-1 ring-stone-200 hover:bg-stone-100">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                    Edit product
                </a>

                @if ($product->is_active)
                    <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Archive {{ $product->name }}? It will be hidden from the POS and alerts.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-rose-600 shadow-sm ring-1 ring-rose-200 hover:bg-rose-50">
                            <x-heroicon-o-archive-box class="h-4 w-4" />
                            Archive
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('products.activate', $product) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
                            <x-heroicon-o-arrow-path class="h-4 w-4" />
                            Activate
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Adjust stock --}}
            <div class="lg:col-span-1">
                <form method="POST" action="{{ route('products.adjust', $product) }}" class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
                    @csrf

                    <h3 class="font-semibold text-stone-900">Adjust stock</h3>
                    <p class="mt-1 text-xs text-stone-500">Record a delivery, wastage, or a physical count.</p>

                    <div class="mt-4 grid grid-cols-3 gap-2">
                        @foreach (['in' => 'Add', 'out' => 'Remove', 'set' => 'Set count'] as $mode => $label)
                            <label class="cursor-pointer">
                                <input type="radio" name="mode" value="{{ $mode }}" class="peer sr-only" @checked(old('mode', 'in') === $mode)>
                                <span class="block rounded-xl border border-stone-200 px-2 py-2 text-center text-xs font-semibold text-stone-600 transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700">
                                    {{ $label }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('mode')" />

                    <div class="mt-3">
                        <x-input-label for="quantity" value="Quantity ({{ $product->unit }})" />
                        <x-text-input id="quantity" name="quantity" type="number" step="0.001" min="0" class="mt-1" :value="old('quantity')" required />
                        <x-input-error :messages="$errors->get('quantity')" />
                    </div>

                    <div class="mt-3">
                        <x-input-label for="note" value="Note (optional)" />
                        <x-text-input id="note" name="note" type="text" class="mt-1" :value="old('note')" placeholder="e.g. Delivery from supplier" />
                        <x-input-error :messages="$errors->get('note')" />
                    </div>

                    <x-primary-button class="mt-4 w-full">
                        <x-heroicon-o-adjustments-horizontal class="h-5 w-5" />
                        Update stock
                    </x-primary-button>
                </form>
            </div>

            {{-- Movement history --}}
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
                        <h3 class="font-semibold text-stone-900">Stock movements</h3>
                        <span class="text-xs text-stone-500">{{ $movements->total() }} records</span>
                    </div>

                    @if ($movements->isEmpty())
                        <p class="px-5 py-10 text-center text-sm text-stone-500">No movements recorded yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-stone-200 text-sm">
                                <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                                    <tr>
                                        <th class="px-5 py-3">Date</th>
                                        <th class="px-5 py-3">Type</th>
                                        <th class="px-5 py-3">Note</th>
                                        <th class="px-5 py-3 text-right">Change</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    @foreach ($movements as $movement)
                                        @php($signed = $movement->signedQuantity())
                                        <tr>
                                            <td class="whitespace-nowrap px-5 py-3 text-stone-600">{{ $movement->created_at->format('d M Y, g:i a') }}</td>
                                            <td class="px-5 py-3">
                                                <x-badge :tone="$movement->type === 'in' ? 'emerald' : ($movement->type === 'out' ? 'rose' : 'sky')">
                                                    {{ $movement->label() }}
                                                </x-badge>
                                            </td>
                                            <td class="px-5 py-3 text-stone-600">{{ $movement->note ?: '—' }}</td>
                                            <td class="whitespace-nowrap px-5 py-3 text-right font-semibold {{ $signed >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                                {{ $signed >= 0 ? '+' : '−' }}{{ rtrim(rtrim(number_format(abs($signed), 3), '0'), '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="border-t border-stone-100 px-5 py-3">
                            {{ $movements->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent sales of this product --}}
        <div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
            <h3 class="font-semibold text-stone-900">Recently sold</h3>

            @if ($sold->isEmpty())
                <p class="mt-3 text-sm text-stone-500">This product has not been sold yet.</p>
            @else
                <ul class="mt-3 divide-y divide-stone-100">
                    @foreach ($sold as $item)
                        <li class="flex items-center justify-between gap-3 py-2.5">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-stone-800">{{ $item->sale?->invoice_no ?? 'Sale removed' }}</p>
                                <p class="text-xs text-stone-500">{{ $item->sale?->sale_date?->format('d M Y, g:i a') }}</p>
                            </div>
                            <p class="text-sm text-stone-600">
                                {{ rtrim(rtrim(number_format((float) $item->quantity, 3), '0'), '.') }} × <x-money :value="$item->unit_price" />
                                = <strong class="text-stone-900"><x-money :value="$item->line_total" /></strong>
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-app-layout>
