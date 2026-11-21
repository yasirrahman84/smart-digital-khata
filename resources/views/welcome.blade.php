<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Smart Digital Khata — udhaar ledger, stock alerts and payment reminders for Kirana stores.">
    <meta name="theme-color" content="#022c22">

    <title>{{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 font-sans text-stone-800 antialiased">

<header class="mx-auto flex max-w-6xl items-center justify-between px-4 py-5 sm:px-6">
    <a href="{{ route('home') }}" class="flex items-center gap-3">
        <span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-600 text-lg font-bold text-white">ک</span>
        <span>
            <span class="block font-bold leading-tight text-stone-900">Smart Digital Khata</span>
            <span class="block text-[11px] uppercase tracking-wide text-stone-500">Khata · Stock · Reminders</span>
        </span>
    </a>

    <nav class="flex items-center gap-2">
        @auth
            <a href="{{ route('dashboard') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Open dashboard</a>
        @else
            <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-200/60">Log in</a>
            <a href="{{ route('register') }}" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Create account</a>
        @endauth
    </nav>
</header>

<main>
    <section class="mx-auto grid max-w-6xl items-center gap-12 px-4 pb-16 pt-8 sm:px-6 lg:grid-cols-2 lg:pb-24 lg:pt-16">
        <div>
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-800">
                <x-heroicon-s-check-badge class="h-4 w-4" />
                Built for Kirana and general stores
            </span>

            <h1 class="mt-5 text-4xl font-extrabold leading-tight tracking-tight text-stone-900 sm:text-5xl">
                The khata that
                <span class="text-emerald-700">never forgets</span>
                who owes you.
            </h1>

            <p class="mt-5 max-w-xl text-lg leading-relaxed text-stone-600">
                Replace the paper notebook with a simple mobile web app. Record udhaar, track stock,
                and remind customers about payments — without installing anything.
            </p>

            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('register') }}" class="rounded-xl bg-emerald-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                    Start free
                </a>
                <a href="{{ route('login') }}" class="rounded-xl border border-stone-300 bg-white px-6 py-3 text-sm font-semibold text-stone-700 transition hover:border-stone-400">
                    I already have an account
                </a>
            </div>

            <p class="mt-4 text-xs text-stone-500">
                Works on any smartphone browser · No app store · Your data stays yours
            </p>
        </div>

        {{-- Decorative product preview --}}
        <div class="relative mx-auto w-full max-w-sm" aria-hidden="true">
            <div class="absolute -inset-6 -z-10 rounded-[2rem] bg-emerald-200/50 blur-2xl"></div>

            <div class="rounded-3xl border border-stone-200 bg-white p-5 shadow-xl shadow-stone-900/5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-stone-400">Total receivable</p>
                        <p class="mt-1 text-3xl font-extrabold text-stone-900">Rs 38,450</p>
                    </div>
                    <span class="grid h-11 w-11 place-items-center rounded-2xl bg-emerald-100 text-emerald-700">
                        <x-heroicon-o-book-open class="h-6 w-6" />
                    </span>
                </div>

                <div class="mt-5 space-y-3">
                    <div class="flex items-center justify-between rounded-xl bg-stone-50 px-3 py-2.5">
                        <span class="flex items-center gap-2.5">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-amber-100 text-xs font-bold text-amber-800">AR</span>
                            <span>
                                <span class="block text-sm font-semibold text-stone-800">Ali Raza</span>
                                <span class="block text-[11px] text-stone-500">Udhaar · 2 days ago</span>
                            </span>
                        </span>
                        <span class="text-sm font-bold text-rose-600">Rs 4,250</span>
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-stone-50 px-3 py-2.5">
                        <span class="flex items-center gap-2.5">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-sky-100 text-xs font-bold text-sky-800">SB</span>
                            <span>
                                <span class="block text-sm font-semibold text-stone-800">Sara Bibi</span>
                                <span class="block text-[11px] text-stone-500">Payment · today</span>
                            </span>
                        </span>
                        <span class="text-sm font-bold text-emerald-600">Rs 1,000</span>
                    </div>

                    <div class="flex items-center justify-between rounded-xl bg-stone-50 px-3 py-2.5">
                        <span class="flex items-center gap-2.5">
                            <span class="grid h-8 w-8 place-items-center rounded-full bg-violet-100 text-xs font-bold text-violet-800">MK</span>
                            <span>
                                <span class="block text-sm font-semibold text-stone-800">M. Kashif</span>
                                <span class="block text-[11px] text-stone-500">Udhaar · 1 week ago</span>
                            </span>
                        </span>
                        <span class="text-sm font-bold text-rose-600">Rs 2,780</span>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-2 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs font-medium text-amber-900">
                    <x-heroicon-o-exclamation-triangle class="h-4 w-4 shrink-0" />
                    Sugar 1kg is low — 3 packets left
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-stone-200 bg-white">
        <div class="mx-auto grid max-w-6xl gap-6 px-4 py-14 sm:grid-cols-2 sm:px-6 lg:grid-cols-3">
            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-6">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-100 text-emerald-700">
                    <x-heroicon-o-book-open class="h-6 w-6" />
                </span>
                <h2 class="mt-4 font-bold text-stone-900">Digital khata</h2>
                <p class="mt-1.5 text-sm leading-relaxed text-stone-600">
                    Every udhaar and every payment in one place, with a running balance you can trust.
                </p>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-6">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-sky-100 text-sky-700">
                    <x-heroicon-o-cube class="h-6 w-6" />
                </span>
                <h2 class="mt-4 font-bold text-stone-900">Stock that warns you</h2>
                <p class="mt-1.5 text-sm leading-relaxed text-stone-600">
                    Set a threshold per item. A sale lowers stock automatically and flags items before they run out.
                </p>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-stone-50 p-6">
                <span class="grid h-11 w-11 place-items-center rounded-xl bg-amber-100 text-amber-700">
                    <x-heroicon-o-device-phone-mobile class="h-6 w-6" />
                </span>
                <h2 class="mt-4 font-bold text-stone-900">Reminders that go out</h2>
                <p class="mt-1.5 text-sm leading-relaxed text-stone-600">
                    Send a polite payment reminder with a private statement link — by SMS or WhatsApp.
                </p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6">
        <div class="grid gap-8 lg:grid-cols-3">
            <div>
                <span class="text-sm font-bold text-emerald-700">01</span>
                <h3 class="mt-2 font-bold text-stone-900">Add your customers</h3>
                <p class="mt-1 text-sm leading-relaxed text-stone-600">Name, phone and an opening balance if they already owe you.</p>
            </div>
            <div>
                <span class="text-sm font-bold text-emerald-700">02</span>
                <h3 class="mt-2 font-bold text-stone-900">Record the sale</h3>
                <p class="mt-1 text-sm leading-relaxed text-stone-600">Cash or udhaar. Stock updates itself and the khata stays in sync.</p>
            </div>
            <div>
                <span class="text-sm font-bold text-emerald-700">03</span>
                <h3 class="mt-2 font-bold text-stone-900">Collect what you're owed</h3>
                <p class="mt-1 text-sm leading-relaxed text-stone-600">See who is overdue and send a reminder in one tap.</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 pb-20 sm:px-6">
        <div class="rounded-3xl bg-emerald-950 px-6 py-12 text-center sm:px-12">
            <h2 class="text-2xl font-extrabold text-white sm:text-3xl">Your shop, in your pocket.</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-emerald-200">
                Set up your khata in a few minutes. No installation, no monthly hardware, no training required.
            </p>
            <a href="{{ route('register') }}" class="mt-7 inline-block rounded-xl bg-emerald-500 px-7 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">
                Create your khata
            </a>
        </div>
    </section>
</main>

<footer class="border-t border-stone-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-xs text-stone-500 sm:flex-row sm:items-center sm:justify-between sm:px-6">
        <p>© {{ date('Y') }} Smart Digital Khata · University of Malakand</p>
        <p>Final year project by Yasir Rahman</p>
    </div>
</footer>

</body>
</html>
