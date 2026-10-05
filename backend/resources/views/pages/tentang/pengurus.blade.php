@php
    $coreBoard = [
        ['nama' => 'Agus Purwadi', 'jabatan' => 'Ketua'],
        ['nama' => 'Kusnul Huda', 'jabatan' => 'Sekretaris'],
        ['nama' => 'Syaiful Rijal Aziz', 'jabatan' => 'Bendahara'],
    ];
    $divisions = [
        'Bidang Pendidikan dan Pengembangan Anak' => ["Lulu Fati'ah", 'Rania Azzahra S.Pd', 'Ratu Cahaya Islami S.Pd', 'Syahra Salsabilla S.Pd'],
        'Bidang Donasi dan Usaha' => ['Ainul Yaqin', 'Al-Adiyat Atthur Robby', 'Hardi Agung', 'Imam Hanafi'],
        'Bidang Kesehatan' => ['Ibu Koyum'],
        'Bidang Hubungan Masyarakat' => ['Tony Misbah'],
        'Bidang Keamanan' => ['Purwanto'],
        'Bidang Pelayanan LKS' => ['Refmawati', 'Siti Romlah'],
    ];
@endphp

<x-layouts.app title="Pengurus">
    <x-page-header title="Pengurus" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Pengurus' => null]" />

    <section class="mx-auto w-full max-w-[1172px] px-4 py-10 font-open text-ink lg:pt-[30px] lg:pb-[60px]">
        <h2 class="font-raleway text-2xl text-ink-dark">Pengurus Inti</h2>
        <ul class="mt-5 grid gap-4 sm:grid-cols-3 sm:gap-[10px]">
            @foreach ($coreBoard as $member)
                <li class="rounded border border-[#e6e6e6] bg-white px-4 py-5 text-center shadow-sm">
                    <p class="text-base text-ink-dark">{{ $member['nama'] }}</p>
                    <hr class="my-3 border-[#e6e6e6]">
                    <p class="text-sm font-bold">{{ $member['jabatan'] }}</p>
                </li>
            @endforeach
        </ul>

        <h2 class="mt-12 font-raleway text-2xl text-ink-dark">Bidang-Bidang</h2>
        <ul class="mt-5 grid gap-4 md:grid-cols-2">
            @foreach ($divisions as $division => $members)
                <li class="rounded border border-[#e6e6e6] bg-white px-6 py-5 shadow-sm">
                    <h3 class="text-center text-base font-bold text-ink-dark">{{ $division }}</h3>
                    <hr class="my-3 border-[#e6e6e6]">
                    <ul class="space-y-1 text-sm">
                        @foreach ($members as $member)
                            <li class="grid grid-cols-[1.5rem_1fr_1.5rem] items-center text-center">
                                <span class="text-left" aria-hidden="true">&bull;</span>
                                <span>{{ $member }}</span>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ul>

        <h2 class="mt-12 font-raleway text-2xl text-ink-dark">Pembina</h2>
        <div class="mt-5 w-full max-w-[320px] rounded border border-[#e6e6e6] bg-white px-6 py-5 shadow-sm">
            <p class="text-base text-ink-dark">Eka Prasetyandani S. Pd. I</p>
            <hr class="my-3 border-[#e6e6e6]">
            <p class="text-sm font-bold">Pembina</p>
        </div>
    </section>
</x-layouts.app>
