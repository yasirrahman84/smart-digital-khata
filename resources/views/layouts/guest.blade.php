<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#022c22">
    <meta name="description" content="Smart Digital Khata — udhaar ledger, stock alerts and payment reminders for Kirana stores.">

    <title>{{ isset($title) ? $title.' · '.config('app.name') : config('app.name') }}</title>

    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 font-sans text-stone-800 antialiased">
    <div class="flex min-h-screen flex-col items-center justify-center px-4 py-10">
        <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3">
            <span class="grid h-12 w-12 place-items-center rounded-2xl bg-emerald-600 text-xl font-bold text-white">ک</span>
            <span>
                <span class="block text-lg font-bold leading-tight text-stone-900">Smart Digital Khata</span>
                <span class="block text-xs text-stone-500">Khata · Stock · Reminders</span>
            </span>
        </a>

        <div class="w-full max-w-md rounded-2xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
            {{ $slot }}
        </div>

        <p class="mt-6 text-xs text-stone-500">© {{ date('Y') }} Smart Digital Khata</p>
    </div>
</body>
</html>
