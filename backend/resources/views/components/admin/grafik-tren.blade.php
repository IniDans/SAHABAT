@props(['tren', 'judul'])

@php
    use App\Models\BeratBadan;

    // Grafik garis SVG yang digambar di server; tooltip hover/fokus diatur initGrafikTren di app.js.
    $lebar = 640;
    $tinggi = 260;
    $kiri = 56;
    $kanan = 616;
    $atas = 20;
    $bawah = 224;

    $titik = $tren['titik']->values();
    $prakiraan = $tren['prakiraan'];
    $jumlahSlot = $titik->count() + ($prakiraan ? 1 : 0);
    $nilai = $titik->pluck('rata')->filter(fn (?float $rata): bool => $rata !== null);

    if ($prakiraan) {
        $nilai->push($prakiraan['nilai']);
    }

    $ada = $nilai->isNotEmpty();
    $min = $ada ? floor($nilai->min() - 0.5) : 0;
    $maks = $ada ? ceil($nilai->max() + 0.5) : 1;

    if ($maks - $min < 2) {
        $maks = $min + 2;
    }

    $langkah = max(1, (int) ceil(($maks - $min) / 4));
    $maks = $min + $langkah * (int) ceil(($maks - $min) / $langkah);

    $x = fn (int $urutan): float => round($kiri + ($kanan - $kiri) * ($jumlahSlot > 1 ? $urutan / ($jumlahSlot - 1) : 0.5), 1);
    $y = fn (float $berat): float => round($bawah - ($bawah - $atas) * ($berat - $min) / ($maks - $min), 1);
    $bulanSingkat = fn ($bulan): string => $bulan->locale('id')->translatedFormat('M');
    $bulanPanjang = fn ($bulan): string => $bulan->locale('id')->translatedFormat('F Y');

    // Garis aktual terputus di bulan tanpa data.
    $segmen = $titik->map(fn (array $item, int $urutan): ?string => $item['rata'] === null ? null : $x($urutan).','.$y($item['rata']))
        ->chunkWhile(fn (?string $sekarang, int $urutan, $sebelumnya): bool => ($sekarang === null) === ($sebelumnya->last() === null))
        ->map(fn ($potongan) => $potongan->filter()->values())
        ->filter(fn ($potongan): bool => $potongan->count() > 1);

    $pita = $titik->map(fn (array $item, int $urutan): array => [...$item, 'urutan' => $urutan])->filter(fn (array $item): bool => $item['idealMin'] !== null);
    $poligonPita = $pita->map(fn (array $item): string => $x($item['urutan']).','.$y($item['idealMaks']))
        ->merge($pita->reverse()->map(fn (array $item): string => $x($item['urutan']).','.$y($item['idealMin'])))
        ->implode(' ');

    $urutanTerakhir = $titik->keys()->filter(fn (int $urutan): bool => $titik[$urutan]['rata'] !== null)->last();
    $lebarSlot = ($kanan - $kiri) / max(1, $jumlahSlot - 1);
@endphp

<div class="relative" data-grafik>
    @if (! $ada)
        <div class="flex h-[260px] flex-col items-center justify-center gap-2 rounded-lg bg-[#fafafa] text-center text-[13px] text-slate">
            Belum ada data berat badan untuk grafik ini.
            <a href="{{ route('admin.kesehatan.timbang') }}" class="font-medium text-footer hover:underline">Input berat badan</a>
        </div>
    @else
        <svg viewBox="0 0 {{ $lebar }} {{ $tinggi }}" class="h-auto w-full overflow-visible" role="img" aria-label="{{ $judul }}, {{ $bulanPanjang($titik->first()['bulan']) }} sampai {{ $bulanPanjang($titik->last()['bulan']) }}. Data lengkap ada di tabel berikut.">
            <defs>
                <clipPath id="grafik-area">
                    <rect x="{{ $kiri - 8 }}" y="{{ $atas }}" width="{{ $kanan - $kiri + 16 }}" height="{{ $bawah - $atas }}" />
                </clipPath>
            </defs>

            @for ($garis = $min; $garis <= $maks; $garis += $langkah)
                <line x1="{{ $kiri - 8 }}" x2="{{ $kanan + 8 }}" y1="{{ $y($garis) }}" y2="{{ $y($garis) }}" stroke="#ebedeb" stroke-width="1" />
                <text x="{{ $kiri - 16 }}" y="{{ $y($garis) + 4 }}" text-anchor="end" font-size="11" fill="#6b7b8d">{{ (int) $garis }} kg</text>
            @endfor

            @if ($poligonPita)
                <polygon points="{{ $poligonPita }}" fill="#00804b" fill-opacity="0.1" clip-path="url(#grafik-area)" />
            @endif

            @foreach ($titik as $urutan => $item)
                <text x="{{ $x($urutan) }}" y="{{ $tinggi - 12 }}" text-anchor="middle" font-size="11" fill="#556270">{{ $bulanSingkat($item['bulan']) }}</text>
            @endforeach

            @foreach ($segmen as $potongan)
                <polyline points="{{ $potongan->implode(' ') }}" fill="none" stroke="#00804b" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" />
            @endforeach

            @if ($prakiraan && $urutanTerakhir !== null)
                @php
                    $xPrakiraan = $x($jumlahSlot - 1);
                    $yPrakiraan = $y($prakiraan['nilai']);
                @endphp
                <line x1="{{ $x($urutanTerakhir) }}" y1="{{ $y($titik[$urutanTerakhir]['rata']) }}" x2="{{ $xPrakiraan }}" y2="{{ $yPrakiraan }}" stroke="#4338ca" stroke-width="2" stroke-dasharray="5 4" stroke-linecap="round" />
                <text x="{{ $xPrakiraan }}" y="{{ $tinggi - 12 }}" text-anchor="middle" font-size="11" font-weight="600" fill="#1e2328">{{ $bulanSingkat($prakiraan['bulan']) }}</text>
            @endif

            @foreach ($titik as $urutan => $item)
                @continue($item['rata'] === null)
                <circle cx="{{ $x($urutan) }}" cy="{{ $y($item['rata']) }}" r="4.5" stroke="#00804b" stroke-width="2" fill="{{ $urutan === $urutanTerakhir ? '#00804b' : '#ffffff' }}" />
            @endforeach

            @if ($prakiraan && $urutanTerakhir !== null)
                <circle cx="{{ $xPrakiraan }}" cy="{{ $yPrakiraan }}" r="5" stroke="#4338ca" stroke-width="2" fill="#ffffff" />
            @endif

            {{-- Label langsung hanya pada nilai terakhir dan prakiraan. --}}
            @if ($urutanTerakhir !== null)
                @php
                    $labelAktual = BeratBadan::kg($titik[$urutanTerakhir]['rata']);
                    $lebarLabel = 12 + mb_strlen($labelAktual) * 6.6;
                    $xLabel = min($x($urutanTerakhir) - $lebarLabel / 2, $kanan + 16 - $lebarLabel);
                    $yLabel = $y($titik[$urutanTerakhir]['rata']) - 34;
                    $yLabel = $yLabel < 0 ? $y($titik[$urutanTerakhir]['rata']) + 12 : $yLabel;
                @endphp
                <rect x="{{ $xLabel }}" y="{{ $yLabel }}" width="{{ $lebarLabel }}" height="22" rx="4" fill="#1e2328" />
                <text x="{{ $xLabel + $lebarLabel / 2 }}" y="{{ $yLabel + 15 }}" text-anchor="middle" font-size="11" font-weight="600" fill="#ffffff">{{ $labelAktual }}</text>
            @endif

            @if ($prakiraan && $urutanTerakhir !== null)
                @php
                    // Cukup angkanya; legenda garis putus-putus sudah menyebut "Prakiraan".
                    $labelPrakiraan = BeratBadan::kg($prakiraan['nilai']);
                    $lebarLabel = 12 + mb_strlen($labelPrakiraan) * 6.6;
                    $xLabel = min($xPrakiraan - $lebarLabel / 2, $kanan + 16 - $lebarLabel);
                    $yLabel = $yPrakiraan - 34 < 0 ? $yPrakiraan + 12 : $yPrakiraan - 34;
                @endphp
                <rect x="{{ $xLabel }}" y="{{ $yLabel }}" width="{{ $lebarLabel }}" height="22" rx="4" fill="#ffffff" stroke="#4338ca" stroke-width="1" />
                <text x="{{ $xLabel + $lebarLabel / 2 }}" y="{{ $yLabel + 15 }}" text-anchor="middle" font-size="11" font-weight="600" fill="#1e2328">{{ $labelPrakiraan }}</text>
            @endif

            {{-- Area hover dan fokus per bulan, lebih lebar dari titiknya. --}}
            @foreach ($titik as $urutan => $item)
                @php
                    $isi = $item['rata'] === null
                        ? ['Belum ada data']
                        : array_filter([
                            'Rata-rata '.BeratBadan::kg($item['rata']),
                            $item['idealMin'] !== null ? 'Ideal '.str_replace(' kg', '', BeratBadan::kg($item['idealMin'])).'–'.BeratBadan::kg($item['idealMaks']) : null,
                        ]);
                @endphp
                <g tabindex="0" class="outline-none" data-grafik-titik data-x="{{ $x($urutan) }}" data-judul="{{ $bulanPanjang($item['bulan']) }}" data-isi="{{ implode("\n", $isi) }}" aria-label="{{ $bulanPanjang($item['bulan']) }}: {{ implode(', ', $isi) }}">
                    <line x1="{{ $x($urutan) }}" x2="{{ $x($urutan) }}" y1="{{ $atas }}" y2="{{ $bawah }}" stroke="#556270" stroke-width="1" stroke-dasharray="2 3" class="opacity-0" data-grafik-garis />
                    <rect x="{{ $x($urutan) - $lebarSlot / 2 }}" y="{{ $atas - 10 }}" width="{{ $lebarSlot }}" height="{{ $bawah - $atas + 20 }}" fill="transparent" />
                </g>
            @endforeach

            @if ($prakiraan)
                <g tabindex="0" class="outline-none" data-grafik-titik data-x="{{ $x($jumlahSlot - 1) }}" data-judul="{{ $bulanPanjang($prakiraan['bulan']) }}" data-isi="Prakiraan {{ BeratBadan::kg($prakiraan['nilai']) }}&#10;Dari garis tren {{ $titik->count() }} bulan" aria-label="{{ $bulanPanjang($prakiraan['bulan']) }}: prakiraan {{ BeratBadan::kg($prakiraan['nilai']) }}">
                    <line x1="{{ $x($jumlahSlot - 1) }}" x2="{{ $x($jumlahSlot - 1) }}" y1="{{ $atas }}" y2="{{ $bawah }}" stroke="#556270" stroke-width="1" stroke-dasharray="2 3" class="opacity-0" data-grafik-garis />
                    <rect x="{{ $x($jumlahSlot - 1) - $lebarSlot / 2 }}" y="{{ $atas - 10 }}" width="{{ $lebarSlot }}" height="{{ $bawah - $atas + 20 }}" fill="transparent" />
                </g>
            @endif
        </svg>

        <div class="pointer-events-none absolute top-0 left-0 hidden min-w-[150px] rounded-lg bg-[#1e2328] px-3 py-2 text-[11px] text-white shadow-card" role="status" data-grafik-tooltip>
            <p class="font-semibold" data-grafik-tooltip-judul></p>
            <p class="whitespace-pre-line text-white/80" data-grafik-tooltip-isi></p>
        </div>

        <table class="sr-only">
            <caption>{{ $judul }}</caption>
            <thead>
                <tr>
                    <th scope="col">Bulan</th>
                    <th scope="col">Rata-rata</th>
                    <th scope="col">Rentang ideal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($titik as $item)
                    <tr>
                        <th scope="row">{{ $bulanPanjang($item['bulan']) }}</th>
                        <td>{{ $item['rata'] === null ? 'Belum ada data' : BeratBadan::kg($item['rata']) }}</td>
                        <td>{{ $item['idealMin'] === null ? '-' : BeratBadan::kg($item['idealMin']).' sampai '.BeratBadan::kg($item['idealMaks']) }}</td>
                    </tr>
                @endforeach
                @if ($prakiraan)
                    <tr>
                        <th scope="row">{{ $bulanPanjang($prakiraan['bulan']) }} (prakiraan)</th>
                        <td>{{ BeratBadan::kg($prakiraan['nilai']) }}</td>
                        <td>-</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif
</div>
