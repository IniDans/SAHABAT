<?php

namespace App\Services;

use App\Enums\StatusDonasi;
use App\Models\Donasi;
use App\Models\ProfilPanti;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DokumenPdf;
use InvalidArgumentException;

/**
 * Kuitansi PDF untuk donasi yang sudah diterima.
 */
class KuitansiDonasi
{
    public static function pdf(Donasi $donasi): DokumenPdf
    {
        if ($donasi->status !== StatusDonasi::Diterima) {
            throw new InvalidArgumentException('Kuitansi hanya untuk donasi yang sudah diterima.');
        }

        return Pdf::loadView('site.donasi.kuitansi', [
            'donasi' => $donasi->loadMissing('kebutuhan'),
            'kontak' => ProfilPanti::bagian('kontak'),
        ])->setPaper('a5', 'landscape');
    }

    public static function namaFile(Donasi $donasi): string
    {
        return "kuitansi-donasi-{$donasi->kunci}.pdf";
    }
}
