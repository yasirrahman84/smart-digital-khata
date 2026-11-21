@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'block w-full rounded-xl border-stone-300 bg-white px-3 py-2.5 text-sm text-stone-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-stone-100']) }}>
    {{ $slot }}
</select>
