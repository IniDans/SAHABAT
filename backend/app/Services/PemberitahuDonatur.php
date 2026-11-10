<?php

namespace App\Services;

use App\Mail\PemberitahuanDonasi;
use App\Models\Donasi;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Kirim email status donasi ke donatur. Dikirim langsung (tanpa antrean) karena hosting
 * tidak menjalankan queue worker; kegagalan kirim dicatat tanpa menggagalkan permintaan.
 */
class PemberitahuDonatur
{
    public function kirim(Donasi $donasi): bool
    {
        if (blank($donasi->email)) {
            return false;
        }

        try {
            Mail::to($donasi->email, $donasi->nama_donatur)->send(new PemberitahuanDonasi($donasi));

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }
}
