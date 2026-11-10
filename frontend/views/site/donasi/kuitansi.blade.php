@php
    use App\Models\Donasi;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kuitansi Donasi {{ $donasi->kunci }}</title>
    <style>
        @page { margin: 28px 36px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1e2328; }
        .kepala { width: 100%; border-bottom: 2px solid #0b7a43; padding-bottom: 8px; }
        .kepala td { vertical-align: middle; }
        .logo { height: 40px; }
        .lembaga { font-size: 9px; color: #5d6670; line-height: 1.4; }
        h1 { margin: 14px 0 2px; font-size: 16px; color: #0b7a43; letter-spacing: 1px; }
        .nomor { margin: 0 0 12px; color: #5d6670; }
        table.rincian { width: 100%; border-collapse: collapse; }
        table.rincian td { padding: 5px 0; border-bottom: 1px solid #e6e6e6; vertical-align: top; }
        table.rincian td.label { width: 130px; color: #5d6670; }
        .nominal { font-size: 15px; font-weight: bold; }
        .kaki { width: 100%; margin-top: 18px; }
        .kaki td { vertical-align: top; }
        .catatan { font-size: 8.5px; color: #5d6670; line-height: 1.5; }
        .ttd { width: 190px; text-align: center; }
    </style>
</head>
<body>
    <table class="kepala">
        <tr>
            <td><img src="{{ public_path('images/logo-yasibu.png') }}" alt="Panti Asuhan YASIBU" class="logo"></td>
            <td class="lembaga" style="text-align: right;">
                {!! nl2br(e($kontak['alamat_footer'] ?? '')) !!}<br>
                {{ $kontak['email'] ?? '' }}
            </td>
        </tr>
    </table>

    <h1>KUITANSI DONASI</h1>
    <p class="nomor">No. {{ $donasi->kunci }}</p>

    <table class="rincian">
        <tr>
            <td class="label">Telah diterima dari</td>
            <td>{{ $donasi->nama_donatur }}</td>
        </tr>
        <tr>
            <td class="label">Sejumlah</td>
            <td class="nominal">{{ Donasi::rupiah($donasi->nominal) }}</td>
        </tr>
        <tr>
            <td class="label">Untuk</td>
            <td>{{ $donasi->tujuan() }}</td>
        </tr>
        <tr>
            <td class="label">Metode pembayaran</td>
            <td>{{ $donasi->metode_pembayaran->value }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal donasi</td>
            <td>{{ $donasi->tanggal_donasi->locale('id')->translatedFormat('j F Y') }}</td>
        </tr>
    </table>

    <table class="kaki">
        <tr>
            <td class="catatan">
                Jazakumullahu khairan. Semoga Allah membalas kebaikan Anda dengan pahala yang berlipat.<br>
                Kuitansi ini dibuat otomatis oleh sistem dan sah tanpa tanda tangan basah.<br>
                Keaslian dapat dicek di {{ route('donasi.validasi', ['kunci' => $donasi->kunci]) }}
            </td>
            <td class="ttd">
                Malang, {{ ($donasi->updated_at ?? now())->locale('id')->translatedFormat('j F Y') }}<br>
                Pengurus Panti Asuhan YASIBU
            </td>
        </tr>
    </table>
</body>
</html>
