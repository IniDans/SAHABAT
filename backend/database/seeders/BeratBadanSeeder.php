<?php

namespace Database\Seeders;

use App\Models\AnakPanti;
use App\Models\BeratBadan;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;

/**
 * Contoh hasil timbang 6 bulan terakhir untuk anak aktif, agar grafik dan skrining
 * di dashboard bisa dicoba saat pengembangan. Bukan data sungguhan; dilewati bila
 * tabel berat_badan sudah berisi.
 */
class BeratBadanSeeder extends Seeder
{
    /**
     * Jumlah bulan contoh, termasuk bulan ini.
     */
    private const JUMLAH_BULAN = 6;

    public function run(): void
    {
        if (BeratBadan::query()->exists()) {
            return;
        }

        $bulanIni = CarbonImmutable::now()->startOfMonth();
        $sekarang = now();
        $baris = [];

        foreach (AnakPanti::query()->aktif()->whereNotNull('Tanggal_Lahir')->get() as $anak) {
            // Acak tetap per anak supaya hasil seeding sama setiap kali dijalankan.
            mt_srand(crc32($anak->NIK));
            $rasio = mt_rand(82, 115) / 100;
            $turunBulanTerakhir = mt_rand(1, 10) === 1;

            for ($urutan = self::JUMLAH_BULAN - 1; $urutan >= 0; $urutan--) {
                $bulan = $bulanIni->subMonths($urutan);
                $median = BeratBadan::median($anak->Jenis_Kelamin, (int) $anak->Tanggal_Lahir->diffInMonths($bulan));

                if ($median === null) {
                    continue;
                }

                $berat = $median * $rasio + mt_rand(-2, 2) / 10;

                if ($turunBulanTerakhir && $urutan === 0) {
                    $berat -= 0.7;
                }

                $baris[] = [
                    'nik' => $anak->NIK,
                    'bulan' => $bulan->toDateString(),
                    'berat_kg' => round(max(BeratBadan::BERAT_MIN, $berat), 1),
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ];
            }
        }

        mt_srand();

        foreach (array_chunk($baris, 500) as $potongan) {
            BeratBadan::query()->insert($potongan);
        }
    }
}
