@php
    $shortcuts = [
        ['label' => 'Home', 'route' => 'beranda'],
        ['label' => 'Anak Asuh', 'route' => 'tentang.anak-asuh'],
        ['label' => 'Program', 'route' => 'program.index'],
        ['label' => 'Donasi', 'route' => 'donasi.formulir'],
    ];
    $about = [
        ['label' => 'Profil Lembaga', 'route' => 'tentang.profil'],
        ['label' => 'Visi Misi', 'route' => 'tentang.visi-misi'],
        ['label' => 'Kepengurusan', 'route' => 'tentang.pengurus'],
        ['label' => 'Gallery', 'route' => 'tentang.galeri'],
        ['label' => 'Hubungi Kami', 'route' => 'tentang.kontak'],
    ];
    $socials = [
        ['label' => 'Facebook', 'icon' => 'facebook.svg', 'width' => 18],
        ['label' => 'Instagram', 'icon' => 'instagram.svg', 'width' => 18],
        ['label' => 'YouTube', 'icon' => 'youtube.svg', 'width' => 18],
        ['label' => 'TikTok', 'icon' => 'tiktok.svg', 'width' => 15.75],
    ];
@endphp

<footer class="bg-footer text-white">
    <div class="container-site grid gap-10 py-12 sm:grid-cols-2 lg:grid-cols-[1.4fr_0.8fr_0.8fr_1fr] lg:gap-8 lg:py-[50px]">
        <div class="flex flex-col items-start lg:items-center">
            <img src="{{ asset('images/logo-yasibu-footer.png') }}" alt="Panti Asuhan YASIBU" width="127" height="120" class="h-[120px] w-auto">
            <address class="mt-4 font-raleway text-sm leading-6 text-white not-italic">
                Jalan Babatan III RT.02/RW.03 Kel,<br>
                Arjowinangun, Kec. Kedungkandang,<br>
                Kota Malang, Jawa Timur 65132
            </address>
        </div>

        @foreach (['Pintasan Link' => $shortcuts, 'Tentang Kami' => $about] as $heading => $links)
            <div>
                <h2 class="font-raleway text-base font-semibold">{{ $heading }}</h2>
                <ul class="mt-4 space-y-3">
                    @foreach ($links as $link)
                        <li>
                            <a href="{{ route($link['route']) }}" class="flex items-center gap-1 font-open text-sm text-white/60 transition hover:text-white">
                                <img src="{{ asset('images/icons/chevron-right.svg') }}" alt="" width="18" height="18">
                                {{ $link['label'] }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach

        <div>
            <h2 class="font-raleway text-base font-semibold">Social Media Kami</h2>
            <ul class="mt-4 flex gap-2">
                @foreach ($socials as $social)
                    <li>
                        <a href="#" class="flex size-9 items-center justify-center rounded bg-white/10 transition hover:bg-white/25" aria-label="{{ $social['label'] }}">
                            <img src="{{ asset('images/icons/'.$social['icon']) }}" alt="" width="{{ $social['width'] }}" height="18">
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="bg-footer-dark px-4 py-5 text-center font-open text-sm">
        &copy; Copyright <strong>YASIBU</strong>. All Rights Reserved
    </div>
</footer>
