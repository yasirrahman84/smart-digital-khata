<x-app-layout>
    <x-slot name="title">Add customer</x-slot>
    <x-slot name="subtitle">Start a khata for a new customer</x-slot>

    <div class="max-w-2xl">
        <form method="POST" action="{{ route('customers.store') }}" class="rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
            @csrf

            <div class="mb-6 flex items-start gap-3 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-900">
                <x-heroicon-o-information-circle class="mt-0.5 h-5 w-5 shrink-0" />
                <p>Only the name is required. You can add a phone number later to send reminders.</p>
            </div>

            @include('customers._form', ['customer' => $customer])

            <div class="mt-6 flex items-center justify-end gap-3 border-t border-stone-100 pt-5">
                <a href="{{ route('customers.index') }}" class="rounded-xl px-4 py-2.5 text-sm font-semibold text-stone-600 hover:bg-stone-100">Cancel</a>
                <x-primary-button>
                    <x-heroicon-o-check class="h-5 w-5" />
                    Save customer
                </x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
