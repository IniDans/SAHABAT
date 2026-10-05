<x-layouts.app title="Visi dan Misi">
    <x-page-header title="Visi dan Misi" variant="brand" :breadcrumbs="['Tentang Kami' => route('tentang.profil'), 'Visi dan Misi' => null]" />

    <section class="mx-auto grid w-full max-w-[1440px] gap-10 px-4 py-10 font-open text-[15px] leading-6 text-ink lg:grid-cols-[minmax(0,1fr)_minmax(0,1.15fr)] lg:gap-[60px] lg:px-[100px] lg:pt-8 lg:pb-[120px]">
        <div>
            <h2 class="font-raleway text-2xl font-bold text-ink-dark uppercase">Motto</h2>
            <p class="mt-4 max-w-[420px] font-raleway text-xl leading-snug">Mewujudkan Kebersamaan, Membangun Kemandirian</p>
        </div>

        <div>
            <h2 class="font-raleway text-2xl font-bold text-ink-dark">Visi</h2>
            <p class="mt-3">Menjadi Yayasan Nirlaba guna mewujudkan Insan Indonesia Bersatu dalam kemandirian</p>

            <h2 class="mt-6 font-raleway text-2xl font-bold text-ink-dark">Misi</h2>
            <ul class="mt-3 list-disc space-y-5 pl-5">
                <li>Mendidik anak yatim dan dhuafa dengan system pendidikan asrama yang berkualitas agar menjadi manusia yang berakhlakul karimah, beraqidah kokoh kuat terhadap Allah SWT dan syariat-Nya.</li>
                <li>Memberikan bimbingan keterampilan kepada umat maupun anak asuh agar mampu menunjang pencapaian prestasi akademik &amp; non akademik.</li>
                <li>Menyelenggarakan program-program untuk memberdayakan potensi umat maupun anak asuh agar memiliki keterampilan tinggi dan bermental entrepreneur.</li>
            </ul>
        </div>
    </section>
</x-layouts.app>
