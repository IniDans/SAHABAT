<x-layouts.app title="Artikel Kegiatan">
    <x-page-header title="Artikel Kegiatan Panti Asuhan" variant="brand" :breadcrumbs="['Artikel Kegiatan' => null]" />

    <section class="container-site py-10 lg:pt-[18px] lg:pb-[60px]">
        <form action="{{ route('artikel.index') }}" method="GET" class="grid gap-4 md:grid-cols-2 md:gap-x-[49px]">
            <label class="sr-only" for="kategori-artikel">Kategori</label>
            <x-form.select id="kategori-artikel" name="kategori" :options="['ramadhan' => 'Ramadhan', ...array_combine(array_map('strtolower', array_keys($categories)), array_keys($categories))]" :selected="request('kategori', 'ramadhan')" class="h-[42px]! rounded-[3px] border-[#dbe0e2] text-[#444]" data-autosubmit />

            <label class="sr-only" for="cari-artikel">Cari artikel</label>
            <input id="cari-artikel" type="search" name="q" value="{{ request('q') }}" placeholder="Cari.." class="form-control">
        </form>

        <div class="mt-8 grid gap-x-[22px] gap-y-[34px] sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($articles as $article)
                <x-post-card :post="$article" :url="route('artikel.show', $article['slug'])" class="lg:min-h-[377px]" />
            @endforeach
        </div>

        <x-pagination class="mt-[46px]" :current="(int) request('page', 1)" :last="7" />
    </section>
</x-layouts.app>
