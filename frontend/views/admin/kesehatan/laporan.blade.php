@php
    use App\Models\BeratBadan;

    $namaBulan = $bulan->locale('id')->translatedFormat('F Y');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Bulanan Anak {{ $namaBulan }}</title>
    <style>
        @page { margin: 32px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e2328; }
        .halaman { page-break-after: always; }
        .halaman:last-child { page-break-after: auto; }
        .kepala { width: 100%; border-bottom: 2px solid #0b7a43; padding-bottom: 8px; }
        .kepala td { vertical-align: middle; }
        .logo { height: 36px; }
        .periode { text-align: right; color: #5d6670; }
        h1 { margin: 14px 0 2px; font-size: 15px; color: #0b7a43; }
        h2 { margin: 16px 0 6px; font-size: 11px; color: #0b7a43; text-transform: uppercase; letter-spacing: .5px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data td, table.data th { padding: 4px 6px; border-bottom: 1px solid #e6e6e6; text-align: left; vertical-align: top; }
        table.data th { background: #f1f3f5; font-weight: bold; }
        table.data td.label { width: 140px; color: #5d6670; padding-left: 0; }
        .angka { text-align: right; }
        .status { font-weight: bold; }
        .status-red { color: #b42318; }
        .status-amber { color: #a15c07; }
        .status-green { color: #0b7a43; }
        .status-gray { color: #5d6670; }
        .kosong { color: #5d6670; font-style: italic; }
        .kaki { margin-top: 18px; font-size: 8.5px; color: #5d6670; }
    </style>
</head>
<body>
    @foreach ($skrining as $hasil)
        @php
            $anak = $hasil['anak'];
            $daftarBerat = $riwayat->get((string) $anak->NIK, collect());
        @endphp
        <div class="halaman">
            <table class="kepala">
                <tr>
                    <td><img src="{{ public_path('images/logo-yasibu.png') }}" alt="Panti Asuhan YASIBU" class="logo"></td>
                    <td class="periode">Laporan bulanan anak<br><strong>{{ $namaBulan }}</strong></td>
                </tr>
            </table>

            <h1>{{ $anak->Nama }}</h1>

            <h2>Identitas</h2>
            <table class="data">
                <tr><td class="label">Jenis kelamin</td><td>{{ $anak->Jenis_Kelamin?->value ?? '-' }}</td></tr>
                <tr><td class="label">Tempat, tanggal lahir</td><td>{{ $anak->Tempat_Lahir ?: '-' }}, {{ $anak->Tanggal_Lahir?->locale('id')->translatedFormat('j F Y') ?? '-' }}</td></tr>
                <tr><td class="label">Usia</td><td>{{ $hasil['umur'] !== null ? $hasil['umur'].' tahun' : '-' }}</td></tr>
                <tr><td class="label">Pendidikan</td><td>{{ $anak->Pendidikan ?: '-' }}</td></tr>
                <tr><td class="label">Wali</td><td>{{ $anak->wali?->Nama_Wali ?? '-' }}</td></tr>
            </table>

            <h2>Kesehatan dan gizi</h2>
            <table class="data">
                <tr>
                    <td class="label">Status gizi</td>
                    <td><span class="status status-{{ $hasil['status']->tone() }}">{{ $hasil['status']->label() }}</span></td>
                </tr>
                <tr><td class="label">Berat bulan ini</td><td>{{ $hasil['berat'] !== null ? BeratBadan::kg($hasil['berat']) : 'Belum ditimbang' }}</td></tr>
                @if ($hasil['perubahan'] !== null)
                    <tr><td class="label">Perubahan dari bulan lalu</td><td>{{ $hasil['perubahan'] > 0 ? '+' : '' }}{{ BeratBadan::kg($hasil['perubahan']) }}</td></tr>
                @endif
                @if ($hasil['median'] !== null)
                    <tr><td class="label">Berat standar usia</td><td>{{ BeratBadan::kg($hasil['median']) }}</td></tr>
                @endif
                <tr><td class="label">Saran</td><td>{{ $hasil['saran'] }}</td></tr>
                @if ($anak->Kesehatan)
                    <tr><td class="label">Catatan kesehatan</td><td>{{ $anak->Kesehatan }}</td></tr>
                @endif
            </table>

            <h2>Riwayat berat 6 bulan</h2>
            @if ($daftarBerat->isEmpty())
                <p class="kosong">Belum ada catatan penimbangan.</p>
            @else
                <table class="data">
                    <thead>
                        <tr><th>Bulan</th><th class="angka">Berat</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($daftarBerat as $catatan)
                            <tr>
                                <td>{{ $catatan->bulan->locale('id')->translatedFormat('F Y') }}</td>
                                <td class="angka">{{ BeratBadan::kg((float) $catatan->berat_kg) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <h2>Kegiatan panti bulan ini</h2>
            @if ($kegiatan->isEmpty())
                <p class="kosong">Tidak ada kegiatan tercatat.</p>
            @else
                <table class="data">
                    <thead>
                        <tr><th>Tanggal</th><th>Kegiatan</th><th>Jenis</th><th>Lokasi</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($kegiatan as $item)
                            <tr>
                                <td>{{ $item->tanggal_kegiatan->format('d/m/Y') }}</td>
                                <td>{{ $item->nama_kegiatan }}</td>
                                <td>{{ $item->jenis_kegiatan?->value ?? '-' }}</td>
                                <td>{{ $item->lokasi ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <p class="kaki">Dibuat {{ now()->locale('id')->translatedFormat('j F Y') }} dari data SAHABAT Panti Asuhan YASIBU. Berisi data pribadi anak, simpan dan bagikan seperlunya.</p>
        </div>
    @endforeach
</body>
</html>
