<x-layouts.app :title="$post->judul">
    <x-site.page-header :title="$post->kategori" variant="brand" :breadcrumbs="['Program' => route('program.index'), $post->kategori => null]" />

    @include('site.partials.post-detail', ['indexRoute' => 'program.index'])
</x-layouts.app>
