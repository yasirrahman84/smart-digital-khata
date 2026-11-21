@props(['route', 'label', 'icon', 'active' => null, 'variant' => 'sidebar'])

@if (Route::has($route))
    @php
        $isActive = request()->routeIs($active ?? $route);

        $classes = $variant === 'bottom'
            ? 'flex flex-col items-center gap-0.5 px-1 py-1.5 text-[11px] font-medium ' . ($isActive ? 'text-emerald-700' : 'text-stone-500')
            : 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition ' . ($isActive ? 'bg-emerald-800 text-white' : 'text-emerald-200 hover:bg-emerald-900 hover:text-white');
    @endphp

    <a href="{{ route($route) }}" {{ $attributes->merge(['class' => $classes]) }}>
        <x-dynamic-component
            :component="'heroicon-o-'.$icon"
            class="{{ $variant === 'bottom' ? 'h-6 w-6' : 'h-5 w-5 shrink-0' }}"
        />
        <span>{{ $label }}</span>
    </a>
@endif
