<x-layouts.app title="Artikel Kegiatan">
    <x-site.page-header title="Artikel Kegiatan Panti Asuhan" variant="brand" :breadcrumbs="$kategori ? ['Artikel Kegiatan' => route('artikel.index'), $kategori => null] : ['Artikel Kegiatan' => null]" />

    <section class="container-site py-10 lg:pt-[18px] lg:pb-[60px]">
        @include('site.partials.post-list', ['indexRoute' => 'artikel.index', 'showRoute' => 'artikel.show', 'kosong' => 'Belum ada artikel pada kategori ini.'])
    </section>
</x-layouts.app>
