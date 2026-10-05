<x-layouts.app :title="$post['title']">
    <x-page-header title="Artikel Kegiatan Panti Asuhan" variant="brand" :breadcrumbs="['Artikel Kegiatan' => route('artikel.index'), 'Ramadhan' => null]" />

    @include('pages.partials.post-detail', ['indexRoute' => 'artikel.index', 'showRoute' => 'artikel.show'])
</x-layouts.app>
