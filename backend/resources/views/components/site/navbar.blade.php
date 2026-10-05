@php
    $menu = [
        ['label' => 'Beranda', 'route' => 'beranda', 'active' => 'beranda'],
        ['label' => 'Donasi', 'active' => 'donasi.*', 'children' => [
            ['label' => 'Formulir Donasi', 'route' => 'donasi.formulir'],
            ['label' => 'Validasi Donasi', 'route' => 'donasi.validasi'],
            ['label' => 'Cara Donasi QRIS', 'route' => 'donasi.qris'],
            ['label' => 'Rekening Donasi', 'route' => 'donasi.rekening'],
        ]],
        ['label' => 'Program', 'active' => 'program.*', 'children' => [
            ['label' => 'Pendidikan', 'route' => 'program.index'],
        ]],
        ['label' => 'Artikel Kegiatan', 'route' => 'artikel.index', 'active' => 'artikel.*'],
        ['label' => 'Tentang Kami', 'active' => 'tentang.*', 'children' => [
            ['label' => 'Profil Lembaga', 'route' => 'tentang.profil'],
            ['label' => 'Visi dan Misi', 'route' => 'tentang.visi-misi'],
            ['label' => 'Kepengurusan', 'route' => 'tentang.pengurus'],
            ['label' => 'Anak Asuh', 'route' => 'tentang.anak-asuh'],
            ['label' => 'Galeri', 'route' => 'tentang.galeri'],
            ['label' => 'Kontak Kami', 'route' => 'tentang.kontak'],
        ]],
    ];
@endphp

<header class="relative z-40 bg-white shadow-nav">
    <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:h-[170px] lg:px-[42px]">
        <a href="{{ route('beranda') }}" class="shrink-0" aria-label="Panti Asuhan YASIBU - Beranda">
            <img src="{{ asset('images/logo-yasibu.png') }}" alt="Panti Asuhan YASIBU" width="267" height="96" class="h-[52px] w-auto lg:h-24">
        </a>

        <nav class="hidden lg:block" aria-label="Navigasi utama">
            <ul class="flex items-center gap-7 xl:gap-[42px]">
                @foreach ($menu as $item)
                    @php($isActive = request()->routeIs($item['active']))
                    @isset($item['children'])
                        <li class="group relative" data-dropdown>
                            <button type="button" class="flex items-center gap-1 py-6 font-poppins text-lg font-medium xl:text-xl {{ $isActive ? 'text-brand-red' : 'text-slate' }} transition hover:text-brand-red" aria-haspopup="true" aria-expanded="false" data-dropdown-toggle>
                                {{ $item['label'] }}
                                <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="" width="20" height="20" class="rotate-180 transition group-hover:rotate-0">
                            </button>
                            <ul class="invisible absolute top-full left-0 min-w-56 translate-y-2 rounded-b border-t-2 border-brand-red bg-white py-2 opacity-0 shadow-card transition group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                                @foreach ($item['children'] as $child)
                                    <li>
                                        <a href="{{ route($child['route']) }}" class="block px-5 py-2.5 font-poppins text-base {{ request()->routeIs($child['route']) ? 'text-brand-red' : 'text-slate' }} hover:bg-surface hover:text-brand-red">{{ $child['label'] }}</a>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li>
                            <a href="{{ route($item['route']) }}" class="font-poppins text-lg font-medium xl:text-xl {{ $isActive ? 'text-brand-red' : 'text-slate' }} transition hover:text-brand-red" @if ($isActive) aria-current="page" @endif>{{ $item['label'] }}</a>
                        </li>
                    @endisset
                @endforeach
            </ul>
        </nav>

        <button type="button" class="inline-flex size-11 items-center justify-center rounded text-slate hover:bg-surface lg:hidden" aria-label="Buka menu" aria-expanded="false" aria-controls="mobile-menu" data-nav-toggle>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
        </button>
    </div>

    <nav id="mobile-menu" class="hidden border-t border-surface bg-white lg:hidden" aria-label="Navigasi seluler" data-nav-menu>
        <ul class="flex flex-col px-4 py-3">
            @foreach ($menu as $item)
                @php($isActive = request()->routeIs($item['active']))
                @isset($item['children'])
                    <li>
                        <button type="button" class="flex w-full items-center justify-between py-3 font-poppins text-base font-medium {{ $isActive ? 'text-brand-red' : 'text-slate' }}" aria-expanded="{{ $isActive ? 'true' : 'false' }}" data-accordion-toggle>
                            {{ $item['label'] }}
                            <img src="{{ asset('images/icons/chevron-down.svg') }}" alt="" width="20" height="20" class="transition {{ $isActive ? '' : 'rotate-180' }}" data-accordion-icon>
                        </button>
                        <ul class="{{ $isActive ? '' : 'hidden' }} mb-2 border-l-2 border-brand-red/30 pl-4" data-accordion-panel>
                            @foreach ($item['children'] as $child)
                                <li>
                                    <a href="{{ route($child['route']) }}" class="block py-2 font-poppins text-[15px] {{ request()->routeIs($child['route']) ? 'text-brand-red' : 'text-slate' }}">{{ $child['label'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @else
                    <li>
                        <a href="{{ route($item['route']) }}" class="block py-3 font-poppins text-base font-medium {{ $isActive ? 'text-brand-red' : 'text-slate' }}">{{ $item['label'] }}</a>
                    </li>
                @endisset
            @endforeach
        </ul>
    </nav>
</header>
