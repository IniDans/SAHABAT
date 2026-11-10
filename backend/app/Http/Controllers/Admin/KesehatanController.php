<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusGizi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BeratBadanRequest;
use App\Models\AnakPanti;
use App\Models\BeratBadan;
use App\Models\KegiatanPanti;
use App\Models\LogAktivitas;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class KesehatanController extends Controller
{
    /**
     * Hasil skrining gizi semua anak aktif untuk satu bulan, dengan filter status dan nama.
     */
    public function index(Request $request): View
    {
        $bulan = $this->bulanDariQuery($request) ?? BeratBadan::bulanTerakhir();
        $skrining = AnakPanti::skriningGizi($bulan);
        $status = $request->query('status');
        $cari = trim((string) $request->query('search'));

        $daftar = $skrining
            ->filter(fn (array $hasil): bool => match ($status) {
                'perhatian' => $hasil['status']->perluPerhatian(),
                null, '' => true,
                default => $hasil['status']->value === $status,
            })
            ->when($cari !== '', fn ($daftar) => $daftar->filter(fn (array $hasil): bool => str_contains(mb_strtolower($hasil['anak']->Nama), mb_strtolower($cari))))
            ->sortBy([
                fn (array $a, array $b): int => $a['status']->urutan() <=> $b['status']->urutan(),
                fn (array $a, array $b): int => $a['anak']->Nama <=> $b['anak']->Nama,
            ])
            ->values();

        return view('admin.kesehatan.index', [
            'bulan' => $bulan,
            'daftar' => $daftar,
            'jumlahAnak' => $skrining->count(),
            'jumlahPerStatus' => collect(StatusGizi::cases())
                ->mapWithKeys(fn (StatusGizi $item): array => [$item->value => $skrining->where('status', $item)->count()]),
            'jumlahPerhatian' => $skrining->filter(fn (array $hasil): bool => $hasil['status']->perluPerhatian())->count(),
        ]);
    }

    /**
     * Form input berat badan satu bulan untuk semua anak aktif, dengan berat bulan lalu sebagai pembanding.
     */
    public function timbang(Request $request): View
    {
        $bulan = $this->bulanDariQuery($request) ?? CarbonImmutable::now()->startOfMonth();

        $berat = BeratBadan::query()
            ->whereIn('bulan', [$bulan->toDateString(), $bulan->subMonth()->toDateString()])
            ->get()
            ->groupBy(fn (BeratBadan $item): string => $item->bulan->toDateString())
            ->map(fn ($daftar) => $daftar->pluck('berat_kg', 'nik'));

        return view('admin.kesehatan.timbang', [
            'bulan' => $bulan,
            'anak' => AnakPanti::query()->aktif()->orderBy('Nama')->get(),
            'beratBulanIni' => $berat->get($bulan->toDateString(), collect()),
            'beratBulanLalu' => $berat->get($bulan->subMonth()->toDateString(), collect()),
        ]);
    }

    /**
     * Simpan berat anak aktif untuk satu bulan. Kolom yang dikosongkan menghapus catatan bulan itu.
     */
    public function simpan(BeratBadanRequest $request): RedirectResponse
    {
        $bulan = CarbonImmutable::createFromFormat('!Y-m', $request->validated('bulan'));

        /** @var array<string, string|null> $berat */
        $berat = $request->validated('berat');

        // NIK berupa angka menjadi kunci integer di array PHP, jadi ubah lagi ke string sebelum dicocokkan.
        $nikAktif = AnakPanti::query()->aktif()
            ->whereIn('NIK', array_map(strval(...), array_keys($berat)))
            ->pluck('NIK');

        $jumlahTersimpan = DB::transaction(function () use ($nikAktif, $berat, $bulan): int {
            $jumlah = 0;

            foreach ($nikAktif as $nik) {
                $nilai = $berat[$nik] ?? null;

                if ($nilai === null || $nilai === '') {
                    BeratBadan::query()->padaBulan($bulan)->where('nik', $nik)->delete();

                    continue;
                }

                BeratBadan::query()->updateOrCreate(
                    ['nik' => $nik, 'bulan' => $bulan->toDateString()],
                    ['berat_kg' => round((float) $nilai, 1)],
                );
                $jumlah++;
            }

            return $jumlah;
        });

        $namaBulan = $bulan->locale('id')->translatedFormat('F Y');

        LogAktivitas::catat(LogAktivitas::AKSI_UBAH, label: "Penimbangan {$namaBulan} ({$jumlahTersimpan} anak)", jenis: 'BeratBadan');

        return to_route('admin.kesehatan.timbang', ['bulan' => $bulan->format('Y-m')])
            ->with('status', "Berat badan {$jumlahTersimpan} anak untuk {$namaBulan} disimpan.");
    }

    /**
     * Laporan bulanan anak dalam PDF, satu halaman per anak: identitas, status gizi bulan itu,
     * riwayat berat 6 bulan, dan kegiatan panti pada bulan itu. ?nik= untuk satu anak saja.
     */
    public function laporan(Request $request): Response
    {
        $bulan = $this->bulanDariQuery($request) ?? BeratBadan::bulanTerakhir();
        $nik = $request->string('nik')->trim()->value();

        $skrining = AnakPanti::skriningGizi($bulan)
            ->when($nik !== '', fn ($daftar) => $daftar->filter(fn (array $hasil): bool => (string) $hasil['anak']->NIK === $nik))
            ->values();

        abort_if($skrining->isEmpty(), 404);

        (new EloquentCollection($skrining->pluck('anak')->all()))->load('wali');

        $riwayat = BeratBadan::query()
            ->whereIn('nik', $skrining->map(fn (array $hasil): string => (string) $hasil['anak']->NIK))
            ->whereBetween('bulan', [$bulan->subMonths(5)->toDateString(), $bulan->toDateString()])
            ->orderBy('bulan')
            ->get()
            ->groupBy(fn (BeratBadan $item): string => (string) $item->nik);

        $kegiatan = KegiatanPanti::query()
            ->whereBetween('tanggal_kegiatan', [$bulan->toDateString(), $bulan->endOfMonth()->toDateString()])
            ->orderBy('tanggal_kegiatan')
            ->get();

        $namaFile = 'laporan-anak-'.$bulan->format('Y-m').($nik !== '' ? '-'.Str::slug($skrining->first()['anak']->Nama) : '').'.pdf';

        return Pdf::loadView('admin.kesehatan.laporan', [
            'bulan' => $bulan,
            'skrining' => $skrining,
            'riwayat' => $riwayat,
            'kegiatan' => $kegiatan,
        ])->setPaper('a4')->download($namaFile);
    }

    /**
     * Bulan dari query ?bulan=YYYY-MM, atau null bila kosong, tidak valid, atau di masa depan.
     */
    private function bulanDariQuery(Request $request): ?CarbonImmutable
    {
        $nilai = $request->query('bulan');

        if (! is_string($nilai) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $nilai)) {
            return null;
        }

        $bulan = CarbonImmutable::createFromFormat('!Y-m', $nilai);

        return $bulan->greaterThan(CarbonImmutable::now()->startOfMonth()) ? null : $bulan;
    }
}
