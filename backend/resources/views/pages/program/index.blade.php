<x-layouts.app title="Program Pendidikan">
    <x-page-header title="Pendidikan" variant="brand" :breadcrumbs="['Program' => route('program.index'), 'Pendidikan' => null]" />

    <section class="container-site py-10 lg:pt-[18px] lg:pb-[60px]">
        <form action="{{ route('program.index') }}" method="GET">
            <label class="sr-only" for="kategori-program">Kategori</label>
            <x-form.select id="kategori-program" name="kategori" :options="['ramadhan' => 'Ramadhan', 'pendidikan' => 'Pendidikan', 'kegiatan' => 'Kegiatan']" selected="ramadhan" class="h-[42px]! rounded-[3px] border-[#dbe0e2] text-[#444]" data-autosubmit />
        </form>

        <div class="mt-5 grid gap-x-[22px] gap-y-[34px] sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($programs as $program)
                <x-post-card :post="$program" :url="route('program.show', $program['slug'])" class="lg:min-h-[388px]" />
            @endforeach
        </div>

        <x-pagination class="mt-[46px]" :current="(int) request('page', 1)" :last="7" />
    </section>
</x-layouts.app>
