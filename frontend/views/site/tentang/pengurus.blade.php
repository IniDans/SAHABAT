<x-layouts.app title="Pengurus">
    <x-site.page-header title="Pengurus" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Pengurus' => null]" />

    <section class="mx-auto w-full max-w-[1172px] px-4 py-10 font-open text-ink lg:pt-[30px] lg:pb-[60px]">
        <h2 class="font-raleway text-xl text-ink-dark">Pengurus Inti</h2>
        <ul class="mt-5 grid gap-4 sm:grid-cols-3 sm:gap-[10px]">
            @foreach ($profil['inti'] as $member)
                <li class="rounded border border-[#e6e6e6] bg-white px-4 py-5 text-center shadow-sm">
                    <p class="text-base text-ink-dark">{{ $member['nama'] }}</p>
                    <hr class="my-3 border-[#e6e6e6]">
                    <p class="text-sm font-bold">{{ $member['jabatan'] }}</p>
                </li>
            @endforeach
        </ul>

        @if ($profil['bidang'])
            <h2 class="mt-12 font-raleway text-xl text-ink-dark">Bidang-Bidang</h2>
            <ul class="mt-5 grid gap-4 md:grid-cols-2">
                @foreach ($profil['bidang'] as $bidang)
                    <li class="rounded border border-[#e6e6e6] bg-white px-6 py-5 shadow-sm">
                        <h3 class="text-center text-base font-bold text-ink-dark">{{ $bidang['nama'] }}</h3>
                        <hr class="my-3 border-[#e6e6e6]">
                        <ul class="list-disc space-y-1 pl-5 text-sm marker:text-brand-green">
                            @foreach ($bidang['anggota'] as $member)
                                <li>{{ $member }}</li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($profil['pembina'])
            <h2 class="mt-12 font-raleway text-xl text-ink-dark">Pembina</h2>
            <ul class="mt-5 flex flex-wrap gap-4 sm:gap-[10px]">
                @foreach ($profil['pembina'] as $member)
                    <li class="w-full max-w-[320px] rounded border border-[#e6e6e6] bg-white px-6 py-5 shadow-sm">
                        <p class="text-base text-ink-dark">{{ $member['nama'] }}</p>
                        <hr class="my-3 border-[#e6e6e6]">
                        <p class="text-sm font-bold">{{ $member['jabatan'] }}</p>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</x-layouts.app>
