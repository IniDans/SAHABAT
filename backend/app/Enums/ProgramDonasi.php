<?php

namespace App\Enums;

enum ProgramDonasi: string
{
    case Zakat = 'Zakat';
    case Infak = 'Infak';
    case Sedekah = 'Sedekah';
    case Wakaf = 'Wakaf';
    case Beasiswa = 'Beasiswa';
}
