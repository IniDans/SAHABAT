<x-layouts.app :title="$post->judul">
    <x-site.page-header title="Artikel Kegiatan Panti Asuhan" variant="brand" :breadcrumbs="['Artikel Kegiatan' => route('artikel.index'), $post->kategori => null]" />

    @include('site.partials.post-detail', ['indexRoute' => 'artikel.index'])
</x-layouts.app>
