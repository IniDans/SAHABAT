<x-layouts.app title="Profil Lembaga">
    <x-page-header title="Profil Lembaga" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Profil Lembaga' => null]" />

    <section class="mx-auto grid w-full max-w-[1440px] gap-10 px-4 py-10 font-open text-[15px] leading-6 text-ink lg:grid-cols-2 lg:gap-[60px] lg:px-[60px] lg:pt-6 lg:pb-[120px]">
        <div>
            <h2 class="font-raleway text-lg leading-tight font-bold text-ink-dark uppercase">{{ $profil['judul_legalitas'] }}</h2>
            <dl class="mt-5 space-y-2">
                @foreach ($profil['legalitas'] as $dokumen)
                    <div><dt class="inline">{{ $dokumen['label'] }}:</dt> <dd class="inline">{{ $dokumen['nilai'] }}</dd></div>
                @endforeach
            </dl>
        </div>

        <div>
            <h2 class="font-raleway text-2xl leading-tight font-bold text-ink-dark uppercase">Profil Lembaga</h2>
            <div class="mt-5 space-y-4">
                {{-- Paragraf dipisah baris kosong; isi di-escape dulu sebelum baris baru diubah ke <br>. --}}
                @foreach (preg_split('/\R\s*\R/', trim($profil['sejarah'])) as $paragraf)
                    <p>{!! nl2br(e(trim($paragraf))) !!}</p>
                @endforeach
            </div>
        </div>
    </section>
</x-layouts.app>
