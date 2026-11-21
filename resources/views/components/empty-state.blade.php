@props(['icon' => 'inbox', 'title', 'description' => null])

<div class="rounded-2xl border border-dashed border-stone-300 bg-white px-6 py-12 text-center">
    <span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-stone-100 text-stone-500">
        <x-dynamic-component :component="'heroicon-o-'.$icon" class="h-6 w-6" />
    </span>

    <h3 class="mt-4 font-semibold text-stone-900">{{ $title }}</h3>

    @if ($description)
        <p class="mx-auto mt-1 max-w-sm text-sm leading-relaxed text-stone-500">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-5">{{ $actions }}</div>
    @endisset
</div>
