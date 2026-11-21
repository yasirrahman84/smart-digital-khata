<x-app-layout>
    <x-slot name="title">Edit {{ $customer->name }}</x-slot>
    <x-slot name="subtitle">Update customer details</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('customers.update', $customer) }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            @include('customers._form', ['customer' => $customer])

            <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-stone-100 pt-5">
                <a href="{{ route('customers.show', $customer) }}" class="text-sm font-semibold text-stone-600 hover:text-stone-900">Back to khata</a>

                <div class="flex items-center gap-3">
                    <a href="{{ route('customers.show', $customer) }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100">Cancel</a>
                    <x-primary-button>
                        <x-heroicon-o-check class="h-5 w-5" />
                        Save changes
                    </x-primary-button>
                </div>
            </div>
        </form>

        <div class="mt-6 rounded-2xl border border-rose-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-stone-900">Remove customer</h2>
            <p class="mt-1 text-sm text-stone-500">
                Removing a customer hides them from your list. A customer with an outstanding balance cannot be removed.
            </p>

            <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="mt-4" onsubmit="return confirm('Remove {{ $customer->name }} from your khata?');">
                @csrf
                @method('DELETE')
                <x-danger-button>
                    <x-heroicon-o-trash class="h-5 w-5" />
                    Remove customer
                </x-danger-button>
            </form>
        </div>
    </div>
</x-app-layout>
