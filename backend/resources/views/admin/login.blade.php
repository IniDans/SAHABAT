@php
    $hasBackground = file_exists(public_path('images/login-bg.jpg'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>Login Admin | Panti Asuhan YASIBU</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col font-poppins">
        <header class="flex h-20 shrink-0 items-center bg-white px-4 sm:px-6 lg:h-[170px] lg:px-[42px]">
            <a href="{{ route('beranda') }}" aria-label="Panti Asuhan YASIBU - Beranda">
                <img src="{{ asset('images/logo-yasibu.png') }}" alt="Panti Asuhan YASIBU" width="267" height="96" class="h-[52px] w-auto lg:h-24">
            </a>
        </header>

        <main class="relative isolate flex flex-1 flex-col items-center justify-center bg-gradient-to-br from-footer-dark via-[#1e2328] to-[#1e2328] px-4 py-12">
            @if ($hasBackground)
                <img src="{{ asset('images/login-bg.jpg') }}" alt="" class="absolute inset-0 -z-20 size-full object-cover">
            @endif
            <div class="absolute inset-0 -z-10 bg-black/55"></div>

            <div class="w-full max-w-[421px] rounded-xl bg-white px-6 pt-3 pb-9 shadow-2xl sm:px-9">
                <img src="{{ asset('images/logo-yasibu.png') }}" alt="" width="100" height="36" class="mx-auto h-9 w-auto">
                <h1 class="mt-2 text-[26px] leading-tight font-semibold text-[#1e2328]">Login Admin</h1>
                <p class="mt-1 text-sm text-slate">Masuk untuk mengelola data panti.</p>

                <form action="{{ route('login.store') }}" method="POST" class="mt-9 space-y-5">
                    @csrf

                    <div>
                        <label for="login" class="block text-sm text-[#1e2328]">Email atau username</label>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autofocus autocomplete="username" placeholder="admin@contoh.com" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror class="mt-1.5 h-[46px] w-full rounded-lg border border-field px-4 text-sm placeholder:text-[#8a929a] focus:border-brand-red focus:ring-4 focus:ring-brand-red/15 focus:outline-none aria-invalid:border-brand-red">
                        @error('login')
                            <p id="login-error" class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm text-[#1e2328]">Password</label>
                        <div class="relative mt-1.5">
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password" class="h-[46px] w-full rounded-lg border border-field pr-12 pl-4 text-sm placeholder:text-[#8a929a] focus:border-brand-red focus:ring-4 focus:ring-brand-red/15 focus:outline-none" data-password-input>
                            <button type="button" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-slate hover:text-[#1e2328]" aria-label="Tampilkan password" aria-pressed="false" data-password-toggle>
                                <x-admin.icon name="eye" width="24" height="24" data-icon-show />
                                <x-admin.icon name="eye-off" width="24" height="24" class="hidden" data-icon-hide />
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1.5 text-xs text-brand-red">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-[#1e2328]">
                            <input type="checkbox" name="remember" class="size-4 rounded border-field accent-brand-red">
                            Ingat saya
                        </label>
                        <a href="#" class="text-slate hover:text-brand-red">Lupa password?</a>
                    </div>

                    <button type="submit" class="flex h-12 w-full items-center justify-center rounded-lg border border-[#a3141c] bg-[#dc3c45] text-sm text-white transition hover:bg-brand-red focus-visible:ring-4 focus-visible:ring-brand-red/30 focus-visible:outline-none">Masuk</button>
                </form>
            </div>

            <a href="{{ route('beranda') }}" class="mt-10 flex items-center gap-2 text-sm text-white/70 hover:text-white">
                <x-admin.icon name="arrow-left" width="16" height="16" />
                Kembali ke Halaman depan
            </a>
        </main>
    </body>
</html>
