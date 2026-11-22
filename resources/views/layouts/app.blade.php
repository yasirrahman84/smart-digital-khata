<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#022c22">
    <meta name="description" content="{{ $description ?? 'Smart Digital Khata — udhaar ledger, stock alerts and payment reminders for Kirana stores.' }}">

    <title>{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:title" content="{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}">
    <meta property="og:description" content="Khata, stock and payment reminders for small shops.">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-stone-100 font-sans text-stone-800 antialiased">
<a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[60] focus:rounded-lg focus:bg-emerald-700 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
    Skip to main content
</a>

<noscript>
    <div class="bg-amber-100 px-4 py-2 text-center text-sm font-medium text-amber-900">
        Please enable JavaScript. The menus, cart and charts need it to work.
    </div>
</noscript>

@php
    $user = auth()->user();

    $navItems = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'home', 'active' => 'dashboard'],
        ['route' => 'customers.index', 'label' => 'Customers', 'icon' => 'users', 'active' => 'customers.*'],
        ['route' => 'khata.index', 'label' => 'Khata', 'icon' => 'book-open', 'active' => 'khata.*'],
        ['route' => 'products.index', 'label' => 'Stock', 'icon' => 'cube', 'active' => 'products.*'],
        ['route' => 'sales.index', 'label' => 'Sales', 'icon' => 'shopping-cart', 'active' => 'sales.*'],
        ['route' => 'reports.receivables', 'label' => 'Reports', 'icon' => 'chart-bar', 'active' => 'reports.*'],
    ];

    $bottomItems = [
        ['route' => 'dashboard', 'label' => 'Home', 'icon' => 'home', 'active' => 'dashboard'],
        ['route' => 'customers.index', 'label' => 'Khata', 'icon' => 'users', 'active' => 'customers.*'],
        ['route' => 'sales.index', 'label' => 'Sales', 'icon' => 'shopping-cart', 'active' => 'sales.*'],
        ['route' => 'products.index', 'label' => 'Stock', 'icon' => 'cube', 'active' => 'products.*'],
    ];
@endphp

<div x-data="{ drawer: false }" class="min-h-screen lg:flex">
    {{-- Desktop sidebar --}}
    <aside class="no-print hidden lg:sticky lg:top-0 lg:flex lg:h-screen lg:w-64 lg:shrink-0 lg:flex-col bg-emerald-950 text-emerald-100">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-5 py-5">
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-emerald-500 text-lg font-bold text-white">ک</span>
            <span class="min-w-0">
                <span class="block truncate font-semibold text-white">{{ $user->store_name ?: $user->name }}</span>
                <span class="block text-xs text-emerald-300">Smart Digital Khata</span>
            </span>
        </a>

        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-3">
            @foreach ($navItems as $item)
                <x-nav-item :route="$item['route']" :label="$item['label']" :icon="$item['icon']" :active="$item['active']" />
            @endforeach
        </nav>

        <div class="space-y-1 border-t border-emerald-900/80 p-3">
            @if (Route::has('settings.edit'))
                <x-nav-item route="settings.edit" label="Settings" icon="cog-6-tooth" active="settings.*" />
            @endif

            <div class="flex items-center gap-3 rounded-xl px-3 py-2">
                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-800 text-sm font-semibold text-white">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block truncate text-sm font-medium text-white">{{ $user->name }}</span>
                    <span class="block truncate text-xs text-emerald-300">{{ $user->email }}</span>
                </span>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-emerald-200 transition hover:bg-emerald-900 hover:text-white">
                    <x-heroicon-o-arrow-left-on-rectangle class="h-5 w-5" />
                    Log out
                </button>
            </form>
        </div>
    </aside>

    {{-- Mobile drawer --}}
    <div x-show="drawer" x-cloak class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true">
        <div class="absolute inset-0 bg-stone-900/50" @click="drawer = false"></div>

        <aside
            x-show="drawer"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full"
            class="absolute inset-y-0 left-0 flex w-72 max-w-[85%] flex-col bg-emerald-950 text-emerald-100"
        >
            <div class="flex items-center justify-between px-5 py-5">
                <span class="flex items-center gap-3">
                    <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-500 text-lg font-bold text-white">ک</span>
                    <span class="font-semibold text-white">Smart Digital Khata</span>
                </span>
                <button type="button" class="rounded-lg p-2 text-emerald-200 hover:bg-emerald-900" @click="drawer = false" aria-label="Close menu">
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-2">
                @foreach ($navItems as $item)
                    <x-nav-item :route="$item['route']" :label="$item['label']" :icon="$item['icon']" :active="$item['active']" />
                @endforeach
            </nav>

            <div class="space-y-1 border-t border-emerald-900/80 p-3">
                @if (Route::has('settings.edit'))
                    <x-nav-item route="settings.edit" label="Settings" icon="cog-6-tooth" active="settings.*" />
                @endif
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-emerald-200 hover:bg-emerald-900 hover:text-white">
                    <x-heroicon-o-user-circle class="h-5 w-5" />
                    Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-emerald-200 transition hover:bg-emerald-900 hover:text-white">
                        <x-heroicon-o-arrow-left-on-rectangle class="h-5 w-5" />
                        Log out
                    </button>
                </form>
            </div>
        </aside>
    </div>

    <div class="flex min-w-0 flex-1 flex-col">
        {{-- Top bar --}}
        <header class="no-print sticky top-0 z-30 border-b border-stone-200 bg-white/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center gap-3 px-4 sm:px-6 lg:px-8">
                <button type="button" class="rounded-lg p-2 text-stone-600 hover:bg-stone-100 lg:hidden" @click="drawer = true" aria-label="Open menu">
                    <x-heroicon-o-bars-3 class="h-6 w-6" />
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate text-base font-semibold text-stone-900 sm:text-lg">
                        {{ $title ?? 'Dashboard' }}
                    </h1>
                    @isset($subtitle)
                        <p class="truncate text-xs text-stone-500">{{ $subtitle }}</p>
                    @endisset
                </div>

                @if (Route::has('notifications.index'))
                    @php $unreadCount = $user->unreadNotifications()->count(); @endphp
                    <a href="{{ route('notifications.index') }}" class="relative rounded-lg p-2 text-stone-600 hover:bg-stone-100" aria-label="Notifications">
                        <x-heroicon-o-bell class="h-6 w-6" />
                        @if ($unreadCount > 0)
                            <span class="absolute right-1 top-1 grid h-4 min-w-4 place-items-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                @endif

                <a href="{{ route('profile.edit') }}" class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-emerald-100 text-sm font-semibold text-emerald-800" title="{{ $user->name }}">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </a>
            </div>
        </header>

        <main id="main-content" class="mx-auto w-full max-w-7xl flex-1 px-4 py-6 pb-24 sm:px-6 lg:px-8 lg:pb-10">
            @if (session('success'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-init="setTimeout(() => show = false, 7000)"
                    x-transition.opacity.duration.300ms
                    role="status"
                    aria-live="polite"
                    class="mb-4 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800"
                >
                    <x-heroicon-o-check-circle class="mt-0.5 h-5 w-5 shrink-0" />
                    <p class="flex-1">{{ session('success') }}</p>
                    <button type="button" @click="show = false" class="-mr-1 rounded-lg p-1 text-emerald-700 hover:bg-emerald-100" aria-label="Dismiss message">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    role="alert"
                    class="mb-4 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800"
                >
                    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0" />
                    <p class="flex-1">{{ session('error') }}</p>
                    <button type="button" @click="show = false" class="-mr-1 rounded-lg p-1 text-rose-700 hover:bg-rose-100" aria-label="Dismiss message">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mb-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    <x-heroicon-o-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0" />
                    <div>
                        <p class="font-semibold">Please check the form and try again.</p>
                        <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Mobile bottom nav --}}
    <nav class="no-print fixed inset-x-0 bottom-0 z-40 border-t border-stone-200 bg-white/95 backdrop-blur lg:hidden">
        <div class="mx-auto flex max-w-md items-stretch justify-around px-2 pb-[env(safe-area-inset-bottom)]">
            @foreach ($bottomItems as $item)
                <x-nav-item :route="$item['route']" :label="$item['label']" :icon="$item['icon']" :active="$item['active']" variant="bottom" class="flex-1" />
            @endforeach

            <button type="button" class="flex flex-1 flex-col items-center gap-0.5 px-1 py-1.5 text-[11px] font-medium text-stone-500" @click="drawer = true">
                <x-heroicon-o-ellipsis-horizontal class="h-6 w-6" />
                <span>More</span>
            </button>
        </div>
    </nav>
</div>

@stack('scripts')
</body>
</html>
