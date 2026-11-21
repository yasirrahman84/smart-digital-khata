<x-app-layout>
    <x-slot name="title">Add product</x-slot>
    <x-slot name="subtitle">Add an item you sell</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('products.store') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf

            @include('products._form', ['product' => $product])

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-stone-100 pt-5">
                <a href="{{ route('products.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100">Cancel</a>
                <x-primary-button>
                    <x-heroicon-o-check class="h-5 w-5" />
                    Save product
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
