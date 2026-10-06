<?php

namespace App\Enums;

enum StatusDonasi: string
{
    case Menunggu = 'Menunggu';
    case Diterima = 'Diterima';
    case Ditolak = 'Ditolak';
}
