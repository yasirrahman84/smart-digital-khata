@props(['current'])

<div class="no-print flex flex-wrap gap-1 rounded-xl border border-stone-200 bg-white p-1 shadow-sm">
    @foreach ([
        'receivables' => ['label' => 'Receivables', 'route' => 'reports.receivables'],
        'sales' => ['label' => 'Sales', 'route' => 'reports.sales'],
        'stock' => ['label' => 'Stock', 'route' => 'reports.stock'],
    ] as $key => $tab)
        <a
            href="{{ route($tab['route']) }}"
            class="rounded-lg px-4 py-2 text-sm font-semibold transition {{ $current === $key ? 'bg-emerald-600 text-white' : 'text-stone-600 hover:bg-stone-100' }}"
        >
            {{ $tab['label'] }}
        </a>
    @endforeach
</div>
