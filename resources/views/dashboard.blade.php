<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="rounded-2xl border border-dashed border-stone-300 bg-white p-10 text-center">
        <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-emerald-100 text-emerald-700">
            <x-heroicon-o-squares-2x2 class="h-6 w-6" />
        </span>
        <h2 class="mt-4 text-lg font-bold text-stone-900">Your dashboard is being set up</h2>
        <p class="mx-auto mt-1 max-w-md text-sm text-stone-500">
            Add your first customer to start a khata. Sales, stock and reminder summaries will appear here.
        </p>
        <a href="{{ route('customers.create') }}" class="mt-5 inline-block rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
            Add a customer
        </a>
    </div>
</x-app-layout>
