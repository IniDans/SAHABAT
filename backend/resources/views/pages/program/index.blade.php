<x-layouts.app :title="$kategori ? 'Program '.$kategori : 'Program'">
    <x-page-header :title="$kategori ?? 'Program'" variant="brand" :breadcrumbs="$kategori ? ['Program' => route('program.index'), $kategori => null] : ['Program' => null]" />

    <section class="container-site py-10 lg:pt-[18px] lg:pb-[60px]">
        @include('pages.partials.post-list', ['indexRoute' => 'program.index', 'showRoute' => 'program.show', 'kosong' => 'Belum ada program pada kategori ini.'])
    </section>
</x-layouts.app>
