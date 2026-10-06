<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\BeratBadanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('berat_badan')]
#[Fillable(['nik', 'bulan', 'berat_kg'])]
class BeratBadan extends Model
{
    /** @use HasFactory<BeratBadanFactory> */
    use HasFactory;

    /**
     * Batas berat yang masuk akal untuk anak panti, dalam kilogram.
     */
    public const BERAT_MIN = 2;

    public const BERAT_MAKS = 150;

    /**
     * Median berat badan (kg) menurut umur dalam tahun penuh: WHO untuk 0–10 tahun,
     * CDC persentil 50 untuk 11–18 tahun. Umur di antaranya diinterpolasi per bulan.
     *
     * @var array<string, list<float>>
     */
    private const MEDIAN_BERAT = [
        'Laki-Laki' => [3.3, 9.6, 12.2, 14.3, 16.3, 18.3, 20.5, 22.9, 25.4, 28.1, 31.2, 35.6, 40.0, 45.3, 50.8, 56.0, 60.8, 64.4, 66.9],
        'Perempuan' => [3.2, 8.9, 11.5, 13.9, 16.1, 18.2, 20.2, 22.4, 25.0, 28.2, 31.9, 36.0, 41.5, 45.8, 49.4, 52.0, 53.5, 54.4, 56.7],
    ];

    /**
     * Rasio berat terhadap median: di bawah RASIO_KURANG berarti gizi kurang,
     * di luar rentang ideal perlu dipantau.
     */
    public const RASIO_KURANG = 0.8;

    public const RASIO_IDEAL_MIN = 0.9;

    public const RASIO_IDEAL_MAKS = 1.2;

    /**
     * Penurunan berat sebulan (kg) yang langsung dianggap gizi kurang.
     */
    public const TURUN_DRASTIS = 0.5;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'bulan' => 'immutable_date:Y-m-d',
            'berat_kg' => 'float',
        ];
    }

    /**
     * Median berat untuk jenis kelamin dan umur dalam bulan, atau null bila tidak diketahui.
     */
    public static function median(?JenisKelamin $jenisKelamin, ?int $umurBulan): ?float
    {
        if (! $jenisKelamin || $umurBulan === null || $umurBulan < 0) {
            return null;
        }

        $tabel = self::MEDIAN_BERAT[$jenisKelamin->value];
        $tahun = intdiv($umurBulan, 12);

        if ($tahun >= count($tabel) - 1) {
            return $tabel[count($tabel) - 1];
        }

        return $tabel[$tahun] + ($tabel[$tahun + 1] - $tabel[$tahun]) * ($umurBulan % 12) / 12;
    }

    /**
     * Berat dalam format Indonesia, mis. "23,4 kg".
     */
    public static function kg(float $nilai): string
    {
        return number_format($nilai, 1, ',', '.').' kg';
    }

    /**
     * Bulan penimbangan terakhir sampai bulan ini, atau bulan ini bila belum ada data.
     */
    public static function bulanTerakhir(): CarbonImmutable
    {
        $bulan = static::query()->where('bulan', '<=', now()->startOfMonth()->toDateString())->max('bulan');

        return CarbonImmutable::parse($bulan ?? now())->startOfMonth();
    }

    /**
     * Penimbangan pada satu bulan tertentu.
     */
    #[Scope]
    protected function padaBulan(Builder $query, CarbonInterface $bulan): void
    {
        $query->where('bulan', $bulan->copy()->startOfMonth()->toDateString());
    }

    public function anak(): BelongsTo
    {
        return $this->belongsTo(AnakPanti::class, 'nik', 'NIK');
    }
}
