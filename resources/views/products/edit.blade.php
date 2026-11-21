<x-app-layout>
    <x-slot name="title">Edit {{ $product->name }}</x-slot>
    <x-slot name="subtitle">Update product details</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('products.update', $product) }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-stone-50 px-4 py-3">
                <p class="text-sm text-stone-600">
                    Current stock:
                    <strong class="text-stone-900">{{ rtrim(rtrim(number_format((float) $product->stock_qty, 3), '0'), '.') }} {{ $product->unit }}</strong>
                </p>
                <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-900">Adjust stock instead &rarr;</a>
            </div>

            @include('products._form', ['product' => $product])

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-stone-100 pt-5">
                <a href="{{ route('products.show', $product) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100">Cancel</a>
                <x-primary-button>
                    <x-heroicon-o-check class="h-5 w-5" />
                    Save changes
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
