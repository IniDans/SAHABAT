<x-layouts.app :title="$post->judul">
    <x-page-header :title="$post->kategori" variant="brand" :breadcrumbs="['Program' => route('program.index'), $post->kategori => null]" />

    @include('pages.partials.post-detail', ['indexRoute' => 'program.index'])
</x-layouts.app>
