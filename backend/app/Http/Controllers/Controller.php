<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

abstract class Controller
{
    /**
     * Get the requested page size, limited to a sane range.
     */
    protected function perPage(Request $request, int $default = 15): int
    {
        return max(1, min(100, $request->integer('per_page', $default)));
    }

    /**
     * Bulan dari query "bulan" (Y-m) sebagai awal bulan. Null berarti semua bulan ("semua").
     */
    protected function bulanDipilih(Request $request, string $bawaan = 'semua'): ?Carbon
    {
        $bulan = $request->string('bulan', $bawaan)->value();

        if (! preg_match('/^\d{4}-\d{2}$/', $bulan)) {
            return null;
        }

        return Carbon::createFromFormat('!Y-m', $bulan);
    }

    /**
     * Dua belas bulan terakhir untuk pilihan filter, mis. ['2026-10' => 'Okt 2026'].
     *
     * @return array<string, string>
     */
    protected function pilihanBulan(): array
    {
        return collect(range(0, 11))
            ->mapWithKeys(function (int $mundur): array {
                $bulan = now()->startOfMonth()->subMonths($mundur)->locale('id');

                return [$bulan->format('Y-m') => $bulan->translatedFormat('M Y')];
            })
            ->all();
    }
}
