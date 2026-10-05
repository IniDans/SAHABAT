<?php

namespace App\Enums;

/**
 * Cara nama donatur ditampilkan di daftar donatur publik.
 */
enum TampilanDonatur: string
{
    case NamaAsli = 'Nama asli';
    case HambaAllah = 'Hamba Allah';
    case Anonim = 'Anonim';
}
