@props(['title' => 'Dashboard'])

@php
    $menu = [
        null => [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Profil panti', 'icon' => 'building', 'route' => null],
        ],
        'Konten' => [
            ['label' => 'Kelola artikel kegiatan', 'icon' => 'file-text', 'route' => null],
            ['label' => 'Kelola program', 'icon' => 'clipboard', 'route' => null],
            ['label' => 'Kelola galeri', 'icon' => 'image', 'route' => null],
        ],
        'Layanan' => [
            ['label' => 'Kebutuhan panti', 'icon' => 'package', 'route' => null],
            ['label' => 'Donasi', 'icon' => 'heart', 'route' => null],
            ['label' => 'Pesan masuk', 'icon' => 'mail', 'route' => null, 'badge' => 7],
        ],
        'Data' => [
            ['label' => 'Data anak panti', 'icon' => 'users', 'route' => null],
        ],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex">

        <title>{{ $title }} | Admin YASIBU</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-[#f3f4f2] font-poppins text-[#1e2328]">
        <header class="flex h-20 shrink-0 items-center gap-3 bg-white px-4 sm:px-6 lg:h-[170px] lg:px-[42px]">
            <button type="button" class="flex size-10 items-center justify-center rounded-lg text-slate hover:bg-surface lg:hidden" aria-label="Buka menu admin" aria-controls="admin-sidebar" aria-expanded="false" data-sidebar-toggle>
                <x-admin.icon name="menu" width="24" height="24" />
            </button>
            <a href="{{ route('beranda') }}" aria-label="Panti Asuhan YASIBU - Beranda">
                <img src="{{ asset('images/logo-yasibu.png') }}" alt="Panti Asuhan YASIBU" width="267" height="96" class="h-[52px] w-auto lg:h-24">
            </a>
        </header>

        <div class="relative flex flex-1">
            <div class="fixed inset-0 z-30 bg-black/40 lg:hidden" hidden data-sidebar-backdrop></div>

            <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-60 -translate-x-full flex-col bg-footer px-3 py-6 text-white transition-transform duration-200 lg:static lg:translate-x-0" data-sidebar>
                <nav class="flex-1 space-y-5 text-[13px]" aria-label="Menu admin">
                    @foreach ($menu as $section => $items)
                        <div>
                            @if ($section)
                                <p class="mb-2 px-3 text-[11px] tracking-wider text-white/60 uppercase">{{ $section }}</p>
                            @endif
                            <ul class="space-y-1">
                                @foreach ($items as $item)
                                    @php
                                        $isActive = $item['route'] && request()->routeIs($item['route']);
                                    @endphp
                                    <li>
                                        <a href="{{ $item['route'] ? route($item['route']) : '#' }}" @class([
                                            'flex items-center gap-3 rounded-lg px-3 py-2.5 transition',
                                            'bg-black/30 font-semibold' => $isActive,
                                            'hover:bg-white/10' => ! $isActive,
                                        ]) @if ($isActive) aria-current="page" @endif>
                                            <x-admin.icon :name="$item['icon']" class="shrink-0" />
                                            <span class="flex-1">{{ $item['label'] }}</span>
                                            @isset($item['badge'])
                                                <span class="flex size-5 items-center justify-center rounded-full bg-[#dc3c45] text-[11px] font-semibold">{{ $item['badge'] }}</span>
                                            @endisset
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </nav>

                <a href="{{ route('admin.login') }}" class="mt-8 flex items-center gap-3 rounded-lg px-3 py-2.5 text-[13px] hover:bg-white/10">
                    <x-admin.icon name="log-out" class="shrink-0" />
                    Keluar
                </a>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <div class="flex items-center justify-end gap-4 px-4 pt-5 sm:px-8">
                    <button type="button" class="flex size-9 items-center justify-center rounded-full text-slate hover:bg-white" aria-label="Notifikasi">
                        <x-admin.icon name="bell" width="20" height="20" />
                    </button>
                    <a href="#" class="relative flex size-9 items-center justify-center rounded-full text-slate hover:bg-white" aria-label="Pesan masuk (7 belum dibaca)">
                        <x-admin.icon name="mail" width="20" height="20" />
                        <span class="absolute top-1.5 right-1.5 size-2 rounded-full bg-[#dc3c45] ring-2 ring-[#f3f4f2]"></span>
                    </a>
                    <span class="flex size-9 items-center justify-center rounded-full bg-[#2e7d5b] text-sm font-semibold text-white" aria-label="Admin">A</span>
                </div>

                <main class="flex-1 px-4 pt-4 pb-10 sm:px-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
