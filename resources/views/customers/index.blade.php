<x-app-layout>
    <x-slot name="title">Customers</x-slot>
    <x-slot name="subtitle">{{ $summary['count'] }} customers · Rs {{ number_format($summary['receivable']) }} total receivable</x-slot>

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-1 flex-col gap-2 sm:flex-row">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 grid w-10 place-items-center text-stone-500">
                    <x-heroicon-o-magnifying-glass class="h-5 w-5" />
                </span>
                <x-text-input name="q" type="search" class="pl-10" :value="$search" placeholder="Search by name or phone…" />
            </div>

            <x-select name="filter" class="sm:w-40" onchange="this.form.submit()">
                <option value="all" @selected($filter === 'all')>All customers</option>
                <option value="owing" @selected($filter === 'owing')>Owes money</option>
                <option value="clear" @selected($filter === 'clear')>Settled</option>
                <option value="inactive" @selected($filter === 'inactive')>Inactive</option>
            </x-select>

            <x-select name="sort" class="sm:w-44" onchange="this.form.submit()">
                <option value="name" @selected($sort === 'name')>Sort: Name</option>
                <option value="balance" @selected($sort === 'balance')>Sort: Highest balance</option>
                <option value="balance_asc" @selected($sort === 'balance_asc')>Sort: Lowest balance</option>
                <option value="recent" @selected($sort === 'recent')>Sort: Newest</option>
            </x-select>

            <x-secondary-button type="submit" class="sm:hidden">Apply</x-secondary-button>
        </form>

        <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700">
            <x-heroicon-o-plus class="h-5 w-5" />
            Add customer
        </a>
    </div>

    @if ($customers->isEmpty())
        <div class="mt-6">
            <x-empty-state
                icon="users"
                :title="$search !== '' ? 'No customers match your search' : 'Your khata is empty'"
                :description="$search !== '' ? 'Try a different name or phone number.' : 'Add your first customer to start recording udhaar and payments.'"
            >
                <x-slot name="actions">
                    <a href="{{ route('customers.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        <x-heroicon-o-plus class="h-5 w-5" />
                        Add your first customer
                    </a>
                </x-slot>
            </x-empty-state>
        </div>
    @else
        {{-- Desktop table --}}
        <div class="mt-6 hidden overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm sm:block">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                        <th class="px-5 py-3">Customer</th>
                        <th class="px-5 py-3">Phone</th>
                        <th class="px-5 py-3 text-right">Balance</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($customers as $customer)
                        <tr class="hover:bg-stone-50">
                            <td class="px-5 py-3.5">
                                <a href="{{ route('customers.show', $customer) }}" class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-800">
                                        {{ $customer->initials() }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate font-medium text-stone-900">{{ $customer->name }}</span>
                                        @unless ($customer->is_active)
                                            <span class="text-xs text-stone-500">Inactive</span>
                                        @endunless
                                    </span>
                                </a>
                            </td>
                            <td class="px-5 py-3.5 text-sm text-stone-600">{{ $customer->phone ?: '—' }}</td>
                            <td class="px-5 py-3.5 text-right">
                                @if ($customer->balance > 0)
                                    <span class="font-semibold text-rose-600"><x-money :value="$customer->balance" /></span>
                                @elseif ($customer->balance < 0)
                                    <span class="font-semibold text-emerald-600"><x-money :value="$customer->balance" /></span>
                                @else
                                    <x-badge tone="emerald">Settled</x-badge>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right">
                                <a href="{{ route('customers.show', $customer) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-700 hover:text-emerald-900">
                                    View khata
                                    <x-heroicon-o-chevron-right class="h-4 w-4" />
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="mt-6 space-y-3 sm:hidden">
            @foreach ($customers as $customer)
                <a href="{{ route('customers.show', $customer) }}" class="block rounded-2xl border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex min-w-0 items-center gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-full bg-emerald-100 text-sm font-bold text-emerald-800">
                                {{ $customer->initials() }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-stone-900">{{ $customer->name }}</p>
                                <p class="truncate text-xs text-stone-500">{{ $customer->phone ?: 'No phone' }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if ($customer->balance > 0)
                                <p class="font-bold text-rose-600"><x-money :value="$customer->balance" /></p>
                                <p class="text-[11px] text-stone-500">owes</p>
                            @elseif ($customer->balance < 0)
                                <p class="font-bold text-emerald-600"><x-money :value="$customer->balance" /></p>
                                <p class="text-[11px] text-stone-500">advance</p>
                            @else
                                <x-badge tone="emerald">Settled</x-badge>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">
            {{ $customers->links() }}
        </div>
    @endif
</x-app-layout>
