<x-layouts.app title="Visi dan Misi">
    <x-page-header title="Visi dan Misi" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Visi dan Misi' => null]" />

    <section class="mx-auto grid w-full max-w-[1440px] gap-10 px-4 py-10 font-open text-[15px] leading-6 text-ink lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] lg:gap-[60px] lg:px-[100px] lg:pt-8 lg:pb-[120px]">
        <div>
            <h2 class="font-raleway text-2xl font-bold text-ink-dark uppercase">Motto</h2>
            <p class="mt-4 max-w-[420px] font-raleway text-xl leading-snug">{{ $profil['motto'] }}</p>
        </div>

        <div>
            <h2 class="font-raleway text-2xl font-bold text-ink-dark">Visi</h2>
            <p class="mt-3">{{ $profil['visi'] }}</p>

            <h2 class="mt-6 font-raleway text-2xl font-bold text-ink-dark">Misi</h2>
            <ul class="mt-3 list-disc space-y-5 pl-5">
                @foreach ($profil['misi'] as $misi)
                    <li>{{ $misi }}</li>
                @endforeach
            </ul>
        </div>
    </section>
</x-layouts.app>
