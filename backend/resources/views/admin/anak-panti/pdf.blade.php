<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Data Anak Panti Asuhan YASIBU</title>
    <style>
        @page { margin: 28px 28px 36px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1e2328; }
        h1 { margin: 0; font-size: 14px; }
        .meta { margin: 2px 0 12px; color: #5d6670; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #0b7a43; color: #fff; font-weight: bold; text-align: left; }
        th, td { padding: 4px 5px; border: 1px solid #d9dcd9; vertical-align: top; }
        tr:nth-child(even) td { background: #f5faf7; }
        .nomor { width: 18px; text-align: right; }
    </style>
</head>
<body>
    <h1>Data Anak Panti Asuhan YASIBU</h1>
    <p class="meta">Diunduh {{ now()->locale('id')->translatedFormat('j F Y, H:i') }} &middot; {{ $anak->count() }} anak</p>

    <table>
        <thead>
            <tr>
                <th class="nomor">No</th>
                @foreach ($kolom as $judul)
                    <th>{{ $judul }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($anak as $item)
                <tr>
                    <td class="nomor">{{ $loop->iteration }}</td>
                    @foreach ($kolom as $kunci => $judul)
                        <td>{{ $item->nilaiEkspor($kunci) }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($kolom) + 1 }}">Tidak ada data.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
