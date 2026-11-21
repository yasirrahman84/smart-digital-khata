@props(['label', 'value', 'hint' => null, 'tone' => 'emerald', 'icon' => 'chart-bar'])

@php
    $tones = [
        'emerald' => 'bg-emerald-100 text-emerald-700',
        'rose' => 'bg-rose-100 text-rose-700',
        'sky' => 'bg-sky-100 text-sky-700',
        'amber' => 'bg-amber-100 text-amber-700',
        'stone' => 'bg-stone-100 text-stone-600',
    ];
@endphp

<div class="rounded-2xl border border-stone-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-wide text-stone-500">{{ $label }}</p>
            <p class="mt-2 truncate text-2xl font-extrabold text-stone-900">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-stone-500">{{ $hint }}</p>
            @endif
        </div>

        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl {{ $tones[$tone] ?? $tones['stone'] }}">
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-5 w-5" />
        </span>
    </div>
</div>
