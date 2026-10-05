<?php

namespace App\Enums;

enum ProgramDonasi: string
{
    case Zakat = 'Zakat';
    case Pendidikan = 'Pendidikan';
    case Ramadhan = 'Ramadhan';
    case InfaqSedekah = 'Infaq & Sedekah';
}
