<?php

namespace App\Enums;

enum StatusPesan: string
{
    case BelumDibaca = 'Belum dibaca';
    case Dibaca = 'Dibaca';
    case Dibalas = 'Dibalas';
    case Diarsipkan = 'Diarsipkan';

    /**
     * Bentuk untuk kalimat, mis. "Pesan ditandai sudah dibalas."
     */
    public function label(): string
    {
        return match ($this) {
            self::BelumDibaca => 'belum dibaca',
            self::Dibaca => 'sudah dibaca',
            self::Dibalas => 'sudah dibalas',
            self::Diarsipkan => 'diarsipkan',
        };
    }
}
