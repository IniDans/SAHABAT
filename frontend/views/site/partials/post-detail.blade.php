{{--
    Detail tulisan (artikel/program) dengan sidebar.
    Variabel: $post (Berita|Program), $categories, $recentPosts, $donors, $programDonasi, $indexRoute.
    Isi tulisan sudah dibersihkan saat disimpan (TulisanRequest::bersihkanIsi), jadi aman dicetak apa adanya.
--}}
@php
    use App\Enums\ProgramDonasi;
    use App\Models\Donasi;

    $kartu = $post->kartu();
@endphp

<div class="mx-auto grid w-full max-w-[1244px] gap-[30px] px-4 py-10 lg:grid-cols-[minmax(0,1fr)_303px] lg:pt-[30px] lg:pb-[60px]">
    <article class="h-fit bg-white p-5 shadow-card">
        @if ($post->gambarUrl())
            <img src="{{ $post->gambarUrl() }}" alt="{{ $post->judul }}" class="aspect-video w-full object-cover">
        @endif

        <h1 @class(['font-raleway text-2xl leading-tight font-bold text-ink md:text-[32px] md:leading-[38px]', 'mt-5' => $post->gambarUrl()])>{{ $post->judul }}</h1>

        <p class="mt-3 flex items-center gap-2 font-open text-base text-muted">
            <img src="{{ asset('images/icons/calendar.svg') }}" alt="" width="14" height="14" class="size-4">
            <time datetime="{{ $kartu['date_iso'] }}">{{ $kartu['date'] }}</time>
        </p>

        <div class="prose-yasibu mt-5">
            {!! $post->isi !!}
        </div>

        <footer class="mt-6 flex items-center gap-2 border-t border-[#e6e6e6] pt-4">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="text-[#a4afba]" aria-hidden="true"><path d="M3 6a2 2 0 0 1 2-2h4.17a2 2 0 0 1 1.42.59L12 6h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6Z" /></svg>
            <span class="sr-only">Kategori:</span>
            <a href="{{ route($indexRoute, ['kategori' => $post->kategori]) }}" class="font-open text-sm text-[#a4afba] hover:text-brand-red">{{ $post->kategori }}</a>
        </footer>
    </article>

    <aside class="h-fit bg-white p-[30px] shadow-card" aria-label="Sidebar">
        <section>
            <h2 class="font-raleway text-xl font-bold text-slate">Search</h2>
            <form action="{{ route($indexRoute) }}" method="GET" class="mt-4 flex" role="search">
                <label for="sidebar-search" class="sr-only">Cari</label>
                <input id="sidebar-search" type="search" name="q" class="h-10 min-w-0 flex-1 rounded-l border border-slate px-3 font-open text-sm focus:ring-2 focus:ring-slate/30 focus:outline-none">
                <button type="submit" class="flex h-10 w-[46px] items-center justify-center rounded-r bg-slate hover:bg-ink-dark" aria-label="Cari">
                    <img src="{{ asset('images/icons/search.svg') }}" alt="" width="16.02" height="16">
                </button>
            </form>
        </section>

        @if ($categories)
            <section class="mt-8">
                <h2 class="font-raleway text-xl font-bold text-slate">Categories</h2>
                <ul class="mt-3">
                    @foreach ($categories as $name => $count)
                        <li>
                            <a href="{{ route($indexRoute, ['kategori' => $name]) }}" @class(['flex h-[34px] items-center gap-1 font-open text-base hover:text-brand-red', 'text-brand-red' => $name === $post->kategori, 'text-slate-light' => $name !== $post->kategori])>
                                {{ $name }} <span class="text-sm text-muted">({{ $count }})</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if ($recentPosts->isNotEmpty())
            <section class="mt-8">
                <h2 class="font-raleway text-xl font-bold text-slate">Artikel Kegiatan</h2>
                <ul class="mt-4 space-y-5">
                    @foreach ($recentPosts as $recent)
                        @php($recentKartu = $recent->kartu())
                        <li>
                            <a href="{{ route('artikel.show', $recent->slug) }}" class="group flex gap-3">
                                <img src="{{ asset($recentKartu['image']) }}" alt="" class="size-20 shrink-0 object-cover" loading="lazy">
                                <span class="flex flex-col">
                                    <span class="font-raleway text-[15px] leading-[18px] font-bold text-ink-dark group-hover:text-brand-red">{{ $recent->judul }}</span>
                                    <time datetime="{{ $recentKartu['date_iso'] }}" class="mt-1 font-open text-sm text-muted italic">{{ $recentKartu['date'] }}</time>
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="mt-8" data-donor-table data-per-halaman="5">
            <h2 class="font-raleway text-xl font-bold text-slate">Daftar Donatur</h2>

            <div class="mt-4 flex items-center gap-2">
                <label for="donor-search" class="font-open text-sm text-ink">Cari:</label>
                <input id="donor-search" type="search" class="h-8 min-w-0 flex-1 rounded border border-field px-2 font-open text-sm focus:border-[#86b7fe] focus:outline-none" data-donor-search>
            </div>

            <div class="mt-3">
                <label for="donor-program" class="sr-only">Program donasi</label>
                <x-form.select id="donor-program" placeholder="Semua program" :options="collect(ProgramDonasi::cases())->mapWithKeys(fn ($program) => [$program->value => $program->value])" :selected="$programDonasi?->value" class="h-[38px] text-sm" data-donor-program />
            </div>

            <table class="mt-3 w-full font-open text-sm">
                <caption class="sr-only">Daftar donatur</caption>
                <tbody>
                    @foreach ($donors as $donor)
                        <tr class="border-t border-[#dee2e6] even:bg-black/5" data-donor-row data-program="{{ $donor->program->value }}">
                            <td class="p-2">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-[#212529]">{{ $donor->namaTampil() }}</span>
                                    <span class="shrink-0 text-xs text-placeholder">{{ $donor->tanggal_donasi->format('d/m/Y') }}</span>
                                </div>
                                <p class="font-raleway text-xl font-bold text-[#212529]">{{ Donasi::rupiah($donor->nominal) }}</p>
                                <p class="font-raleway font-medium text-placeholder italic">{{ $donor->program->value }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <p class="mt-3 font-open text-sm text-muted" @if ($donors->isNotEmpty()) hidden @endif data-donor-kosong>Belum ada donatur.</p>

            <nav class="mt-3 flex" aria-label="Halaman donatur">
                @foreach (['pertama' => ['«', 'Halaman pertama'], 'sebelumnya' => ['‹', 'Sebelumnya'], 'berikutnya' => ['›', 'Berikutnya'], 'terakhir' => ['»', 'Halaman terakhir']] as $aksi => [$symbol, $label])
                    <button type="button" class="-ml-px flex size-8 items-center justify-center border border-[#dee2e6] text-sm text-placeholder first:ml-0 first:rounded-l last:rounded-r hover:bg-surface disabled:opacity-50" aria-label="{{ $label }}" disabled data-donor-halaman="{{ $aksi }}">{{ $symbol }}</button>
                @endforeach
            </nav>

            <p class="mt-3 font-open text-sm text-ink" aria-live="polite" data-donor-info>Menampilkan {{ $donors->count() }} donatur</p>
        </section>
    </aside>
</div>
