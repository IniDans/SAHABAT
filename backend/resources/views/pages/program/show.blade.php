<x-layouts.app :title="$post['title']">
    <x-page-header title="Pendidikan" variant="brand" :breadcrumbs="['Program' => route('program.index'), 'Pendidikan' => null]" />

    @include('pages.partials.post-detail', ['indexRoute' => 'program.index', 'showRoute' => 'program.show'])
</x-layouts.app>
