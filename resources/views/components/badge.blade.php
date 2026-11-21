@props(['tone' => 'stone'])

@php
    $tones = [
        'stone' => 'bg-stone-100 text-stone-700',
        'emerald' => 'bg-emerald-100 text-emerald-800',
        'rose' => 'bg-rose-100 text-rose-700',
        'amber' => 'bg-amber-100 text-amber-800',
        'sky' => 'bg-sky-100 text-sky-800',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold '.($tones[$tone] ?? $tones['stone'])]) }}>
    {{ $slot }}
</span>
