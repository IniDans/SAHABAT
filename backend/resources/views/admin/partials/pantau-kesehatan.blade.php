@php
    use App\Enums\StatusGizi;
    use App\Models\BeratBadan;
    use Illuminate\Support\Carbon;

    $warnaStatus = [
        StatusGizi::Baik->value => 'bg-footer',
        StatusGizi::Dipantau->value => 'bg-[#e0a526]',
        StatusGizi::Kurang->value => 'bg-[#dc3c45]',
        StatusGizi::BelumDitimbang->value => 'bg-muted',
    ];
    $segmenStatus = collect([StatusGizi::Baik, StatusGizi::Dipantau, StatusGizi::Kurang, StatusGizi::BelumDitimbang])
        ->filter(fn (StatusGizi $status): bool => $status !== StatusGizi::BelumDitimbang || $jumlahPerStatus[$status->value] > 0);
    $judulGrafik = $anakDipilih ? "Tren berat badan {$anakDipilih->Nama}" : 'Tren berat badan rata-rata';
@endphp

<section class="mt-8" aria-labelledby="pantau-kesehatan">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 id="pantau-kesehatan" class="text-lg font-semibold">Pantau kesehatan anak panti</h2>
            <p class="mt-1 text-[13px] text-slate">
                Berat badan ditimbang tiap bulan, lalu dibandingkan dengan standar berat menurut usia.
                @if ($diperbarui)
                    Terakhir diperbarui {{ Carbon::parse($diperbarui)->locale('id')->translatedFormat('j M Y') }}.
                @endif
            </p>
        </div>
        <a href="{{ route('admin.kesehatan.timbang') }}" class="admin-btn admin-btn-primary">
            <x-admin.icon name="plus" width="16" height="16" />
            Input berat badan
        </a>
    </div>

    <div class="grid gap-5 xl:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)]">
        <div class="rounded-xl border border-[#e5e5e5] bg-white p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <h3 class="text-[15px] font-semibold">{{ $judulGrafik }}</h3>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <select name="anak" aria-label="Pilih anak untuk grafik" class="admin-input w-auto min-w-[160px] pr-8" data-autosubmit>
                        <option value="">Semua anak</option>
                        @foreach ($pilihanAnak as $nik => $nama)
                            <option value="{{ $nik }}" @selected($anakDipilih?->NIK === $nik)>{{ $nama }}</option>
                        @endforeach
                    </select>
                    <noscript><button type="submit" class="admin-btn admin-btn-secondary">Tampilkan</button></noscript>
                </form>
            </div>

            <ul class="mt-3 mb-4 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate" aria-label="Keterangan grafik">
                <li class="flex items-center gap-2"><span class="h-0.5 w-5 rounded-full bg-footer" aria-hidden="true"></span>{{ $anakDipilih ? 'Berat aktual' : 'Rata-rata aktual' }}</li>
                <li class="flex items-center gap-2"><span class="w-5 border-t-2 border-dashed border-[#4338ca]" aria-hidden="true"></span>Prakiraan</li>
                <li class="flex items-center gap-2"><span class="h-3 w-5 rounded-sm bg-footer/10" aria-hidden="true"></span>Rentang ideal</li>
            </ul>

            <x-admin.grafik-tren :tren="$tren" :judul="$judulGrafik" />
        </div>

        <div class="flex flex-col rounded-xl border border-[#e5e5e5] bg-white p-5">
            <div class="flex items-center justify-between gap-3">
                <h3 class="flex items-center gap-2 text-[15px] font-semibold">
                    <span class="flex size-8 items-center justify-center rounded-lg bg-[#e3f1ea] text-footer" aria-hidden="true">
                        <x-admin.icon name="activity" width="16" height="16" />
                    </span>
                    Analisis gizi
                </h3>
                <x-admin.badge>{{ $bulan->locale('id')->translatedFormat('M Y') }}</x-admin.badge>
            </div>

            <div class="mt-4 space-y-1 rounded-lg bg-[#f3f6f4] px-4 py-3 text-[13px] leading-relaxed">
                @foreach ($ringkasan as $kalimat)
                    <p>{{ $kalimat }}</p>
                @endforeach
            </div>

            <p class="mt-5 text-[13px] font-semibold">Status gizi {{ $jumlahAnak }} anak</p>
            @if ($jumlahAnak)
                <div class="mt-2 flex h-2.5 gap-0.5 overflow-hidden rounded-full" aria-hidden="true">
                    @foreach ($segmenStatus as $status)
                        @continue($jumlahPerStatus[$status->value] === 0)
                        <div class="{{ $warnaStatus[$status->value] }}" style="flex-grow: {{ $jumlahPerStatus[$status->value] }}"></div>
                    @endforeach
                </div>
            @endif
            <ul class="mt-3 space-y-2 text-[13px]">
                @foreach ($segmenStatus as $status)
                    <li class="flex items-center gap-2">
                        <span class="size-2.5 rounded-full {{ $warnaStatus[$status->value] }}" aria-hidden="true"></span>
                        <a href="{{ route('admin.kesehatan.index', ['status' => $status->value]) }}" class="flex-1 hover:underline">{{ $status->label() }}</a>
                        <span class="font-semibold">{{ $jumlahPerStatus[$status->value] }} anak</span>
                    </li>
                @endforeach
            </ul>

            <a href="{{ route('admin.kesehatan.index') }}" class="admin-btn admin-btn-secondary mt-5 justify-center">Lihat hasil semua anak</a>
            <p class="mt-3 text-[11px] text-slate">Hasil analisis membantu skrining awal, bukan pengganti pemeriksaan tenaga kesehatan.</p>
        </div>
    </div>

    <div class="mt-5 rounded-xl border border-[#e5e5e5] bg-white">
        <div class="flex items-center justify-between gap-3 px-5 py-4">
            <h3 class="text-[15px] font-semibold">Anak yang perlu perhatian</h3>
            @if ($jumlahPerhatian)
                <a href="{{ route('admin.kesehatan.index', ['status' => 'perhatian']) }}" class="text-xs font-semibold text-footer hover:underline">Lihat semua {{ $jumlahPerhatian }} anak</a>
            @endif
        </div>
        @include('admin.partials.tabel-gizi', ['daftar' => $perhatian, 'bulan' => $bulan, 'kosong' => 'Tidak ada anak yang perlu perhatian pada '.$bulan->locale('id')->translatedFormat('F Y').'.'])
    </div>
</section>
