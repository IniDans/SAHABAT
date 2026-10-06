@props(['title', 'heading', 'description' => null])

@php
    $hasBackground = file_exists(public_path('images/login-bg.jpg'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>{{ $title }} | Panti Asuhan YASIBU</title>

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
                <h1 class="mt-2 text-[26px] leading-tight font-semibold text-[#1e2328]">{{ $heading }}</h1>
                @if ($description)
                    <p class="mt-1 text-sm text-slate">{{ $description }}</p>
                @endif

                @session('status')
                    <p class="mt-5 rounded-lg border border-[#bfe3cf] bg-[#e3f1ea] px-4 py-3 text-[13px] text-footer-dark" role="status">{{ $value }}</p>
                @endsession

                {{ $slot }}
            </div>

            <a href="{{ route('beranda') }}" class="mt-10 flex items-center gap-2 text-sm text-white/70 hover:text-white">
                <x-admin.icon name="arrow-left" width="16" height="16" />
                Kembali ke Halaman depan
            </a>
        </main>
    </body>
</html>
