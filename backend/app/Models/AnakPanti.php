<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use App\Enums\StatusAsuh;
use App\Enums\StatusGizi;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\AnakPantiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

#[Table('anak_panti', key: 'NIK', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable([
    'NIK', 'Nama', 'Jenis_Kelamin', 'Tempat_Lahir', 'Tanggal_Lahir', 'Agama',
    'Status_Anak', 'ID_Wali', 'Keterangan', 'Ukuran_Pakaian', 'Ukuran_Sepatu',
    'Kesehatan', 'Pendidikan', 'Status_Asuh',
])]
class AnakPanti extends Model
{
    /** @use HasFactory<AnakPantiFactory> */
    use HasFactory;

    /**
     * Kolom yang bisa dipilih saat export: kunci => judul kolom.
     *
     * @var array<string, string>
     */
    public const KOLOM_EKSPOR = [
        'nik' => 'NIK',
        'nama' => 'Nama',
        'jenis_kelamin' => 'Jenis kelamin',
        'tempat_lahir' => 'Tempat lahir',
        'tanggal_lahir' => 'Tanggal lahir',
        'agama' => 'Agama',
        'status_anak' => 'Status anak',
        'pendidikan' => 'Pendidikan',
        'ukuran_pakaian' => 'Ukuran pakaian',
        'ukuran_sepatu' => 'Ukuran sepatu',
        'kesehatan' => 'Kesehatan',
        'status_asuh' => 'Status asuh',
        'kategori' => 'Kategori',
        'wali' => 'Nama wali',
    ];

    /**
     * Pilihan bawaan untuk form; nilai lain yang sudah ada di database ikut ditampilkan.
     *
     * @var array<string, list<string>>
     */
    public const PILIHAN = [
        'Agama' => ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'],
        'Keterangan' => ['Yatim', 'Piatu', 'Yatim Piatu', 'Dhuafa'],
        'Status_Anak' => ['Balita', 'Pelajar'],
        'Pendidikan' => ['Belum Bersekolah', 'TK', 'SD', 'SMP', 'SMA/SMK', 'Kuliah', 'Sekolah', 'Sudah Lulus'],
        'Kesehatan' => ['Sehat'],
        'Ukuran_Pakaian' => ['S ANAK', 'M ANAK', 'L ANAK', 'XL ANAK', 'S DEWASA', 'M DEWASA', 'L DEWASA', 'XL DEWASA'],
    ];

    /**
     * Default yang sama dengan database, supaya respons setelah create langsung lengkap.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'Keterangan' => 'Dhuafa',
        'Status_Asuh' => 'Masih Aktif',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Jenis_Kelamin' => JenisKelamin::class,
            'Tanggal_Lahir' => 'date:Y-m-d',
            'Status_Asuh' => StatusAsuh::class,
        ];
    }

    /**
     * Anak yang masih diasuh panti.
     */
    #[Scope]
    protected function aktif(Builder $query): void
    {
        $query->where('Status_Asuh', StatusAsuh::MasihAktif);
    }

    /**
     * Sama dengan trigger trg_cegah_hapus_anak_aktif: anak aktif tidak boleh dihapus.
     */
    public function isDeletable(): bool
    {
        return $this->Status_Asuh !== StatusAsuh::MasihAktif;
    }

    /**
     * Pilihan untuk satu kolom: pilihan bawaan ditambah nilai yang sudah tersimpan.
     *
     * @return list<string>
     */
    public static function pilihan(string $kolom): array
    {
        $tersimpan = static::query()->toBase()->whereNotNull($kolom)->distinct()->orderBy($kolom)->pluck($kolom);

        return collect(self::PILIHAN[$kolom] ?? [])
            ->merge($tersimpan)
            ->map(fn ($nilai): string => trim((string) $nilai))
            ->filter()
            ->unique(fn (string $nilai): string => mb_strtolower($nilai))
            ->values()
            ->all();
    }

    /**
     * Umur dalam tahun, atau null bila tanggal lahir kosong.
     */
    public function umur(): ?int
    {
        return $this->Tanggal_Lahir?->age;
    }

    /**
     * Ada catatan kesehatan selain "Sehat".
     */
    public function punyaCatatanKesehatan(): bool
    {
        return filled($this->Kesehatan) && strcasecmp($this->Kesehatan, 'Sehat') !== 0;
    }

    /**
     * Nilai satu kolom export dalam bentuk teks.
     */
    public function nilaiEkspor(string $kolom): string
    {
        return (string) match ($kolom) {
            'nik' => $this->NIK,
            'nama' => $this->Nama,
            'jenis_kelamin' => $this->Jenis_Kelamin?->value,
            'tempat_lahir' => $this->Tempat_Lahir,
            'tanggal_lahir' => $this->Tanggal_Lahir?->format('d-m-Y'),
            'agama' => $this->Agama,
            'status_anak' => $this->Keterangan,
            'pendidikan' => $this->Pendidikan,
            'ukuran_pakaian' => $this->Ukuran_Pakaian,
            'ukuran_sepatu' => $this->Ukuran_Sepatu,
            'kesehatan' => $this->Kesehatan,
            'status_asuh' => $this->Status_Asuh?->value,
            'kategori' => $this->Status_Anak,
            'wali' => $this->wali?->Nama_Wali,
        };
    }

    /**
     * Skrining gizi dari berat bulan ini dan bulan sebelumnya, dengan aturan tetap:
     * dibandingkan median berat menurut umur dan jenis kelamin serta perubahan sebulan.
     *
     * @return array{status: StatusGizi, saran: string, umur: int|null, berat: float|null, beratLalu: float|null, perubahan: float|null, median: float|null}
     */
    public function analisisGizi(CarbonInterface $bulan, ?float $berat, ?float $beratLalu): array
    {
        $umurBulan = $this->Tanggal_Lahir ? (int) $this->Tanggal_Lahir->diffInMonths($bulan) : null;
        $median = BeratBadan::median($this->Jenis_Kelamin, $umurBulan);
        $rasio = $berat !== null && $median ? $berat / $median : null;
        $perubahan = $berat !== null && $beratLalu !== null ? round($berat - $beratLalu, 1) : null;
        $masihTumbuh = $umurBulan === null || $umurBulan < 18 * 12;

        [$status, $saran] = match (true) {
            $berat === null => [StatusGizi::BelumDitimbang, 'Timbang bulan ini'],
            $perubahan !== null && $perubahan <= -BeratBadan::TURUN_DRASTIS,
            $rasio !== null && $rasio < BeratBadan::RASIO_KURANG => [StatusGizi::Kurang, 'Periksa ke puskesmas minggu ini'],
            $perubahan !== null && $perubahan < 0 => [StatusGizi::Dipantau, 'Pantau nafsu makan 2 minggu'],
            $perubahan !== null && $perubahan == 0 && $masihTumbuh => [StatusGizi::Dipantau, 'Tambah asupan protein, timbang ulang'],
            $rasio !== null && $rasio < BeratBadan::RASIO_IDEAL_MIN => [StatusGizi::Dipantau, 'Tambah porsi dan lauk bergizi'],
            $rasio !== null && $rasio > BeratBadan::RASIO_IDEAL_MAKS => [StatusGizi::Dipantau, 'Kurangi makanan manis, tambah aktivitas'],
            $this->punyaCatatanKesehatan() => [StatusGizi::Dipantau, "Ikuti catatan kesehatan: {$this->Kesehatan}"],
            default => [StatusGizi::Baik, 'Pertahankan pola makan'],
        };

        return [
            'status' => $status,
            'saran' => $saran,
            'umur' => $umurBulan === null ? null : intdiv($umurBulan, 12),
            'berat' => $berat,
            'beratLalu' => $beratLalu,
            'perubahan' => $perubahan,
            'median' => $median,
        ];
    }

    /**
     * Skrining gizi semua anak aktif untuk satu bulan, urut nama.
     *
     * @return Collection<int, array{anak: AnakPanti, status: StatusGizi, saran: string, umur: int|null, berat: float|null, beratLalu: float|null, perubahan: float|null, median: float|null}>
     */
    public static function skriningGizi(CarbonInterface $bulan): Collection
    {
        $bulan = CarbonImmutable::instance($bulan)->startOfMonth();
        $bulanLalu = $bulan->subMonth();

        $berat = BeratBadan::query()
            ->whereIn('bulan', [$bulan->toDateString(), $bulanLalu->toDateString()])
            ->get()
            ->groupBy('nik')
            ->map(fn ($catatan) => $catatan->mapWithKeys(fn (BeratBadan $item): array => [$item->bulan->toDateString() => $item->berat_kg]));

        return static::query()->aktif()->orderBy('Nama')->get()
            ->map(fn (AnakPanti $anak): array => ['anak' => $anak] + $anak->analisisGizi(
                $bulan,
                $berat->get($anak->NIK)?->get($bulan->toDateString()),
                $berat->get($anak->NIK)?->get($bulanLalu->toDateString()),
            ))
            ->toBase();
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(WaliAnak::class, 'ID_Wali', 'ID_Wali');
    }

    public function beratBadan(): HasMany
    {
        return $this->hasMany(BeratBadan::class, 'nik', 'NIK');
    }
}
