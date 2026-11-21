@props(['value' => 0])

@php
    $amount = (float) $value;
    $decimals = fmod(abs($amount), 1.0) > 0.0001 ? 2 : 0;
@endphp

<span {{ $attributes->merge(['class' => 'money whitespace-nowrap']) }}>
    {{ $amount < 0 ? '−' : '' }}Rs {{ number_format(abs($amount), $decimals) }}
</span>
