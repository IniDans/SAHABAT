<div class="mx-auto grid w-full max-w-[1244px] gap-[30px] px-4 py-10 lg:grid-cols-[minmax(0,1fr)_303px] lg:pt-[30px] lg:pb-[60px]">
    <article class="bg-white p-5 shadow-card">
        <img src="{{ asset($post['image']) }}" alt="{{ $post['title'] }}" class="aspect-video w-full object-cover">

        <h1 class="mt-5 font-raleway text-2xl leading-tight font-bold text-ink md:text-[32px] md:leading-[38px]">{{ $post['title'] }}</h1>

        <p class="mt-3 flex items-center gap-2 font-open text-base text-muted">
            <img src="{{ asset('images/icons/calendar.svg') }}" alt="" width="14" height="14" class="size-4">
            <time datetime="{{ $post['date_iso'] }}">{{ $post['date'] }}</time>
        </p>

        <div class="prose-yasibu mt-5">
            @include('pages.partials.sample-article-body', ['showRoute' => $showRoute])
        </div>

        <footer class="mt-6 flex items-center gap-2 border-t border-[#e6e6e6] pt-4">
            <img src="{{ asset('images/icons/tag.svg') }}" alt="" width="16.02" height="16">
            <a href="{{ route($indexRoute, ['kategori' => 'ramadhan']) }}" class="font-open text-sm text-[#a4afba] hover:text-brand-red">Ramadhan</a>
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

        <section class="mt-8">
            <h2 class="font-raleway text-xl font-bold text-slate">Categories</h2>
            <ul class="mt-3">
                @foreach ($categories as $name => $count)
                    <li>
                        <a href="{{ route($indexRoute, ['kategori' => strtolower($name)]) }}" class="flex h-[34px] items-center gap-1 font-open text-base text-slate-light hover:text-brand-red">
                            {{ $name }} <span class="text-sm text-muted">({{ $count }})</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="mt-8">
            <h2 class="font-raleway text-xl font-bold text-slate">Artikel Kegiatan</h2>
            <ul class="mt-4 space-y-5">
                @foreach ($recentPosts as $recent)
                    <li>
                        <a href="{{ route($showRoute, $recent['slug']) }}" class="group flex gap-3">
                            <img src="{{ asset($recent['image']) }}" alt="" class="size-20 shrink-0 object-cover" loading="lazy">
                            <span class="flex flex-col">
                                <span class="font-raleway text-[15px] leading-[18px] font-bold text-ink-dark group-hover:text-brand-red">{{ $recent['title'] }}</span>
                                <time datetime="{{ $recent['date_iso'] }}" class="mt-1 font-open text-sm text-muted italic">{{ $recent['date'] }}</time>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="mt-8" data-donor-table>
            <h2 class="font-raleway text-xl font-bold text-slate">Daftar Donatur</h2>

            <div class="mt-4 flex items-center gap-2">
                <label for="donor-search" class="font-open text-sm text-ink">Cari:</label>
                <input id="donor-search" type="search" class="h-8 min-w-0 flex-1 rounded border border-field px-2 font-open text-sm focus:border-[#86b7fe] focus:outline-none" data-donor-search>
            </div>

            <div class="mt-3">
                <label for="donor-program" class="sr-only">Program donasi</label>
                <x-form.select id="donor-program" :options="['ramadhan' => 'Ramadhan', 'zakat' => 'ZAKAT', 'pendidikan' => 'Pendidikan']" selected="ramadhan" class="h-[38px] text-sm" />
            </div>

            <table class="mt-3 w-full font-open text-sm">
                <caption class="sr-only">Donatur program Ramadhan</caption>
                <tbody>
                    @foreach ($donors as $donor)
                        <tr class="border-t border-[#dee2e6] odd:bg-black/5" data-donor-row>
                            <td class="p-2">
                                <div class="flex items-start justify-between gap-2">
                                    <span class="text-[#212529]">{{ $donor['nama'] }}</span>
                                    <span class="text-xs text-placeholder">{{ $donor['tanggal'] }}</span>
                                </div>
                                <p class="font-raleway text-xl font-bold text-[#212529]">{{ $donor['nominal'] }}</p>
                                <p class="font-raleway font-medium text-placeholder italic">{{ $donor['pesan'] }}</p>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <nav class="mt-3 flex" aria-label="Halaman donatur">
                @foreach (['«' => 'Halaman pertama', '‹' => 'Sebelumnya', '›' => 'Berikutnya', '»' => 'Halaman terakhir'] as $symbol => $label)
                    <button type="button" class="-ml-px flex size-8 items-center justify-center border border-[#dee2e6] text-sm text-placeholder first:ml-0 first:rounded-l last:rounded-r hover:bg-surface disabled:opacity-50" aria-label="{{ $label }}" disabled>{{ $symbol }}</button>
                @endforeach
            </nav>

            <p class="mt-3 font-open text-sm text-ink">Menampilkan 1-{{ count($donors) }} dari {{ count($donors) }}</p>
        </section>
    </aside>
</div>
