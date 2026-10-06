<?php

namespace App\Http\Controllers\Admin;

use App\Enums\StatusAsuh;
use App\Enums\StatusGizi;
use App\Enums\StatusPesan;
use App\Http\Controllers\Controller;
use App\Models\AnakPanti;
use App\Models\BeratBadan;
use App\Models\Donasi;
use App\Models\KebutuhanPanti;
use App\Models\Pesan;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Jumlah bulan penimbangan yang tampil di grafik tren.
     */
    private const BULAN_TREN = 6;

    /**
     * Tampilkan dashboard admin dengan data ringkasan.
     */
    public function __invoke(Request $request): View
    {
        return view('admin.dashboard', [
            'stats' => $this->stats(),
            'kesehatan' => $this->kesehatan($request),
            'messages' => $this->messages(),
            'needs' => $this->needs(),
        ]);
    }

    /**
     * @return list<array{label: string, value: string, icon: string, tone: string, url: string|null}>
     */
    private function stats(): array
    {
        $donasiBulanIni = (int) Donasi::diterima()
            ->whereBetween('tanggal_donasi', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('nominal');

        return [
            ['label' => 'Anak panti', 'value' => (string) AnakPanti::aktif()->count(), 'icon' => 'users', 'tone' => 'green', 'url' => route('admin.anak-panti.index', ['status' => StatusAsuh::MasihAktif->value])],
            ['label' => 'Kebutuhan mendesak', 'value' => (string) KebutuhanPanti::mendesak()->count(), 'icon' => 'package', 'tone' => 'red', 'url' => route('admin.kebutuhan-panti.index', ['prioritas' => 'Mendesak', 'status' => 'belum'])],
            ['label' => 'Donasi bulan ini', 'value' => $this->rupiahSingkat($donasiBulanIni), 'icon' => 'heart', 'tone' => 'purple', 'url' => route('admin.donasi.index', ['status' => 'Diterima', 'dari' => now()->startOfMonth()->toDateString()])],
        ];
    }

    /**
     * Pantau kesehatan: skrining gizi bulan penimbangan terakhir, tren berat rata-rata,
     * dan ringkasan tertulis. Semuanya dihitung dengan aturan tetap, tanpa layanan AI.
     *
     * @return array<string, mixed>
     */
    private function kesehatan(Request $request): array
    {
        $bulan = BeratBadan::bulanTerakhir();
        $skrining = AnakPanti::skriningGizi($bulan);
        $anakDipilih = $skrining->pluck('anak')->first(fn (AnakPanti $anak): bool => $anak->NIK === $request->query('anak'));
        $tren = $this->tren($bulan, $anakDipilih);
        $perhatian = $skrining->filter(fn (array $hasil): bool => $hasil['status']->perluPerhatian())
            ->sortBy([
                fn (array $a, array $b): int => $a['status']->urutan() <=> $b['status']->urutan(),
                fn (array $a, array $b): int => ($a['perubahan'] ?? 0) <=> ($b['perubahan'] ?? 0),
            ])
            ->values();

        return [
            'bulan' => $bulan,
            'diperbarui' => BeratBadan::query()->max('updated_at'),
            'jumlahPerStatus' => collect(StatusGizi::cases())
                ->mapWithKeys(fn (StatusGizi $status): array => [$status->value => $skrining->where('status', $status)->count()]),
            'jumlahAnak' => $skrining->count(),
            'perhatian' => $perhatian->take(5),
            'jumlahPerhatian' => $perhatian->count(),
            'tren' => $tren,
            'ringkasan' => $this->ringkasan($bulan, $skrining, $tren['titik']),
            'pilihanAnak' => $skrining->mapWithKeys(fn (array $hasil): array => [$hasil['anak']->NIK => $hasil['anak']->Nama]),
            'anakDipilih' => $anakDipilih,
        ];
    }

    /**
     * Berat rata-rata per bulan (semua anak aktif atau satu anak), rentang idealnya,
     * dan prakiraan bulan berikutnya dari garis tren linear.
     *
     * @return array{titik: Collection<int, array{bulan: CarbonImmutable, rata: float|null, idealMin: float|null, idealMaks: float|null}>, prakiraan: array{bulan: CarbonImmutable, nilai: float}|null}
     */
    private function tren(CarbonImmutable $bulan, ?AnakPanti $anak): array
    {
        $awal = $bulan->subMonths(self::BULAN_TREN - 1);

        $catatan = BeratBadan::query()
            ->with('anak')
            ->whereBetween('bulan', [$awal->toDateString(), $bulan->toDateString()])
            ->when($anak, fn ($query) => $query->where('nik', $anak->NIK), fn ($query) => $query->whereHas('anak', fn ($query) => $query->aktif()))
            ->get()
            ->groupBy(fn (BeratBadan $item): string => $item->bulan->toDateString());

        $titik = collect(range(0, self::BULAN_TREN - 1))->map(function (int $urutan) use ($awal, $catatan): array {
            $bulanIni = $awal->addMonths($urutan);
            $isi = $catatan->get($bulanIni->toDateString(), collect());
            $median = $isi->map(fn (BeratBadan $item): ?float => $item->anak->analisisGizi($bulanIni, $item->berat_kg, null)['median'])->filter();

            return [
                'bulan' => $bulanIni,
                'rata' => $isi->isEmpty() ? null : round($isi->avg('berat_kg'), 1),
                'idealMin' => $median->isEmpty() ? null : round($median->avg() * BeratBadan::RASIO_IDEAL_MIN, 1),
                'idealMaks' => $median->isEmpty() ? null : round($median->avg() * BeratBadan::RASIO_IDEAL_MAKS, 1),
            ];
        });

        return [
            'titik' => $titik,
            'prakiraan' => $this->prakiraan($titik, $bulan->addMonth()),
        ];
    }

    /**
     * Garis lurus kuadrat terkecil melalui titik yang ada, diteruskan satu bulan.
     *
     * @param  Collection<int, array{rata: float|null}>  $titik
     * @return array{bulan: CarbonImmutable, nilai: float}|null
     */
    private function prakiraan(Collection $titik, CarbonImmutable $bulan): ?array
    {
        $ada = $titik->pluck('rata')->filter(fn (?float $rata): bool => $rata !== null);

        if ($ada->count() < 2) {
            return null;
        }

        $rataX = $ada->keys()->avg();
        $rataY = $ada->avg();
        $kemiringan = $ada->map(fn (float $y, int $x): float => ($x - $rataX) * ($y - $rataY))->sum()
            / $ada->keys()->map(fn (int $x): float => ($x - $rataX) ** 2)->sum();

        return [
            'bulan' => $bulan,
            'nilai' => round($rataY + $kemiringan * ($titik->count() - $rataX), 1),
        ];
    }

    /**
     * Kalimat ringkasan untuk panel analisis.
     *
     * @param  Collection<int, array<string, mixed>>  $skrining
     * @param  Collection<int, array{rata: float|null}>  $titik
     * @return list<string>
     */
    private function ringkasan(CarbonImmutable $bulan, Collection $skrining, Collection $titik): array
    {
        $namaBulan = $bulan->locale('id')->translatedFormat('F Y');
        $ditimbang = $skrining->whereNotNull('berat');

        if ($ditimbang->isEmpty()) {
            return ['Belum ada data berat badan. Mulai dengan input berat badan bulan ini.'];
        }

        $kalimat = [];
        $perubahan = $skrining->pluck('perubahan')->filter(fn (?float $nilai): bool => $nilai !== null);

        if ($perubahan->isNotEmpty()) {
            $rata = round($perubahan->avg(), 1);
            $kalimat[] = match (true) {
                $rata > 0 => 'Rata-rata berat badan naik '.BeratBadan::kg($rata).' dari bulan lalu.',
                $rata < 0 => 'Rata-rata berat badan turun '.BeratBadan::kg(abs($rata)).' dari bulan lalu.',
                default => 'Rata-rata berat badan tetap dari bulan lalu.',
            };
        }

        // Selisih rata-rata antarbulan yang berurutan: bandingkan bulan terakhir dengan bulan-bulan sebelumnya.
        $selisih = $titik->pluck('rata')->sliding(2)
            ->filter(fn (Collection $pasangan): bool => $pasangan->every(fn (?float $rata): bool => $rata !== null))
            ->map(fn (Collection $pasangan): float => $pasangan->last() - $pasangan->first())
            ->values();

        if ($selisih->count() >= 3) {
            $terakhir = $selisih->pop();
            $sebelumnya = $selisih->avg();

            if ($terakhir < $sebelumnya - 0.05) {
                $kalimat[] = 'Laju kenaikan melambat dibanding bulan-bulan sebelumnya.';
            } elseif ($terakhir > $sebelumnya + 0.05) {
                $kalimat[] = 'Laju kenaikan lebih cepat dibanding bulan-bulan sebelumnya.';
            }
        }

        $jumlahPerhatian = $skrining->filter(fn (array $hasil): bool => $hasil['status']->perluPerhatian())->count();
        $kalimat[] = $jumlahPerhatian
            ? "{$jumlahPerhatian} anak perlu dipantau lebih dekat bulan ini."
            : 'Semua anak yang ditimbang dalam kondisi baik.';

        $belum = $skrining->count() - $ditimbang->count();

        if ($belum > 0) {
            $kalimat[] = "{$belum} anak belum ditimbang pada {$namaBulan}.";
        }

        return $kalimat;
    }

    /**
     * Lima pesan terbaru dari formulir Kontak.
     *
     * @return list<array{name: string, email: string, subject: string, time: string, unread: bool}>
     */
    private function messages(): array
    {
        return Pesan::query()
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Pesan $pesan): array => [
                'name' => $pesan->nama,
                'email' => $pesan->email,
                'subject' => $pesan->subjek ?: str($pesan->isi)->limit(60)->value(),
                'time' => $pesan->created_at->locale('id')->translatedFormat('j M, H:i'),
                'unread' => $pesan->status === StatusPesan::BelumDibaca,
            ])
            ->all();
    }

    /**
     * Empat kebutuhan teratas, dari yang paling mendesak.
     *
     * @return list<array{name: string, level: string, score: int, fulfilled: bool}>
     */
    private function needs(): array
    {
        return KebutuhanPanti::query()
            ->palingMendesak()
            ->limit(4)
            ->get()
            ->map(fn (KebutuhanPanti $kebutuhan): array => [
                'name' => $kebutuhan->nama,
                'level' => $kebutuhan->prioritas->value,
                'score' => $kebutuhan->skor_prioritas,
                'fulfilled' => $kebutuhan->terpenuhi,
            ])
            ->all();
    }

    /**
     * Nominal ringkas untuk kartu statistik, mis. "Rp 12,5 jt".
     */
    private function rupiahSingkat(int $nominal): string
    {
        foreach ([1_000_000_000 => 'M', 1_000_000 => 'jt'] as $satuan => $label) {
            if ($nominal >= $satuan) {
                $angka = rtrim(rtrim(number_format($nominal / $satuan, 1, ',', '.'), '0'), ',');

                return "Rp {$angka} {$label}";
            }
        }

        return Donasi::rupiah($nominal);
    }
}
