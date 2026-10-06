<?php

namespace App\Enums;

/**
 * Hasil skrining berat badan bulanan, dihitung dengan aturan tetap (tanpa layanan AI).
 */
enum StatusGizi: string
{
    case Kurang = 'kurang';
    case Dipantau = 'dipantau';
    case Baik = 'baik';
    case BelumDitimbang = 'belum';

    public function label(): string
    {
        return match ($this) {
            self::Kurang => 'Gizi kurang',
            self::Dipantau => 'Perlu dipantau',
            self::Baik => 'Gizi baik',
            self::BelumDitimbang => 'Belum ditimbang',
        };
    }

    /**
     * Warna badge, sesuai komponen x-admin.badge.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Kurang => 'red',
            self::Dipantau => 'amber',
            self::Baik => 'green',
            self::BelumDitimbang => 'gray',
        };
    }

    /**
     * Urutan di daftar: yang paling butuh tindakan lebih dulu.
     */
    public function urutan(): int
    {
        return match ($this) {
            self::Kurang => 0,
            self::Dipantau => 1,
            self::BelumDitimbang => 2,
            self::Baik => 3,
        };
    }

    /**
     * Anak dengan status ini masuk daftar "Anak yang perlu perhatian".
     */
    public function perluPerhatian(): bool
    {
        return $this === self::Kurang || $this === self::Dipantau;
    }
}
