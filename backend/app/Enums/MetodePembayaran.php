<?php

namespace App\Enums;

enum MetodePembayaran: string
{
    case Bca = 'BCA';
    case Qris = 'QRIS';
    case Mandiri = 'Mandiri';
    case Bsi = 'BSI';
    case Bri = 'BRI';
    case Bni = 'BNI';
    case BankJatim = 'Bank Jatim';
}
