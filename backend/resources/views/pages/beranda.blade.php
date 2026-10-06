@php
    $slides = [
        [
            'title' => 'TERAMPIL DAN MANDIRI',
            'text' => 'Mendidik anak yatim, piatu dan dhuafa dengan sistem pendidikan asrama yang berkualitas agar menjadi manusia yang bersifat mulia.',
            'caption' => 'Mewujudkan insan dengan kemandirian',
        ],
        [
            'title' => 'BERSATU DALAM KEMANDIRIAN',
            'text' => 'Menjadi Yayasan Nirlaba guna mewujudkan Insan Indonesia Bersatu dalam kemandirian.',
            'caption' => 'Mewujudkan Kebersamaan, Membangun Kemandirian',
        ],
        [
            'title' => 'BERAKHLAKUL KARIMAH',
            'text' => 'Memberikan bimbingan keterampilan kepada umat maupun anak asuh agar mampu menunjang pencapaian prestasi akademik & non akademik.',
            'caption' => 'Membangun generasi berakhlakul karimah',
        ],
    ];
@endphp

<x-layouts.app title="Beranda">
    {{-- Hero slider --}}
    <section class="relative isolate overflow-hidden" aria-roledescription="carousel" aria-label="Sorotan Panti Asuhan YASIBU" data-slider>
        <img src="{{ asset('images/hero-1.png') }}" alt="" class="absolute inset-0 -z-20 size-full object-cover">
        <div class="absolute inset-0 -z-10 bg-[rgba(30,35,40,0.6)]"></div>

        <div class="grid min-h-[640px] md:min-h-[760px] xl:min-h-[946px]">
            @foreach ($slides as $slide)
                <div @class([
                    'col-start-1 row-start-1 flex flex-col items-center px-4 pt-16 pb-12 text-center text-white transition-opacity duration-700 md:pt-24 xl:pt-[132px]',
                    'opacity-100' => $loop->first,
                    'pointer-events-none opacity-0' => ! $loop->first,
                ]) role="group" aria-roledescription="slide" aria-label="{{ $loop->iteration }} dari {{ count($slides) }}" @unless ($loop->first) aria-hidden="true" @endunless data-slide>
                    <h1 class="font-raleway text-3xl leading-tight font-bold md:text-5xl md:leading-[57.6px]">{{ $slide['title'] }}</h1>

                    <div class="mt-6 flex min-h-[260px] w-full max-w-[780px] items-center justify-center rounded-[10px] bg-white/27 px-6 py-10 shadow-hero backdrop-blur-[2px] md:mt-3 md:min-h-[360px] xl:min-h-[447px] xl:px-[60px]">
                        <p class="max-w-[680px] font-open text-base leading-6 md:text-xl md:leading-6">{{ $slide['text'] }}</p>
                    </div>

                    <p class="mt-[88px] font-raleway text-2xl font-medium md:text-[32px] md:leading-tight">{{ $slide['caption'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="absolute inset-x-0 bottom-[120px] flex justify-center gap-1.5 md:bottom-[150px] xl:bottom-[224px]">
            @foreach ($slides as $slide)
                <button type="button" @class(['h-3 w-5 rounded-full transition', 'bg-brand-red' => $loop->first, 'bg-white/60' => ! $loop->first]) aria-label="Tampilkan slide {{ $loop->iteration }}" @if ($loop->first) aria-current="true" @endif data-slider-dot></button>
            @endforeach
        </div>
    </section>

    {{-- Program: hanya program terbit yang dipilih "Tampil di Beranda" --}}
    @if ($programs->isNotEmpty())
        <section class="bg-surface py-12 lg:pt-[25px] lg:pb-[60px]" aria-labelledby="program-heading">
            <x-section-heading eyebrow="Program" title="Program Pendidikan" id="program-heading" />

            <div class="container-site mt-6 grid gap-x-[22px] gap-y-[34px] sm:grid-cols-2 lg:mt-[7px] lg:grid-cols-3">
                @foreach ($programs as $program)
                    <x-post-card :post="$program->kartu()" :url="route('program.show', $program->slug)" with-excerpt class="lg:min-h-[488px]" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Artikel kegiatan --}}
    <section class="bg-surface py-12 lg:pt-[25px] lg:pb-[60px]" aria-labelledby="artikel-heading">
        <x-section-heading eyebrow="Artikel Kegiatan" title="Artikel Kegiatan Panti Asuhan" id="artikel-heading" />

        <form action="{{ route('artikel.index') }}" method="GET" class="container-site mt-6 grid gap-4 md:grid-cols-2 md:gap-x-[49px]">
            <label class="sr-only" for="kategori">Kategori</label>
            <x-form.select id="kategori" name="kategori" placeholder="Pilih Kategori" :options="array_combine(array_keys($categories), array_keys($categories))" class="h-[42px]! rounded-[3px] border-[#dbe0e2] text-[#444]" data-autosubmit />

            <label class="sr-only" for="cari-artikel">Cari artikel</label>
            <input id="cari-artikel" type="search" name="q" placeholder="Cari.." class="form-control">
        </form>

        <div class="container-site mt-[46px] grid gap-x-[22px] gap-y-[34px] sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <x-post-card :post="$article->kartu()" :url="route('artikel.show', $article->slug)" class="lg:min-h-[377px]" />
            @endforeach
        </div>

        @if ($articles->isEmpty())
            <p class="container-site py-12 text-center font-open text-base text-muted">Belum ada artikel kegiatan.</p>
        @else
            <div class="mt-[46px] flex justify-center">
                <a href="{{ route('artikel.index') }}" class="inline-flex h-10 items-center rounded bg-brand-red px-6 font-open text-sm text-white transition hover:bg-[#b81d26]">Lihat semua artikel</a>
            </div>
        @endif
    </section>
</x-layouts.app>
