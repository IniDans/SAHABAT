<?php

namespace App\Models\Concerns;

use App\Models\LogAktivitas;

/**
 * Catat setiap tambah, ubah, dan hapus oleh pengguna yang login ke tabel log_aktivitas.
 * Model bisa menimpa labelLog() untuk nama yang tampil di log dan kolomTanpaLog()
 * untuk kolom yang perubahannya tidak perlu dicatat.
 */
trait TercatatDiLog
{
    protected static function bootTercatatDiLog(): void
    {
        static::created(fn (self $model) => LogAktivitas::catat(LogAktivitas::AKSI_TAMBAH, $model));

        static::updated(function (self $model): void {
            $kolom = array_values(array_diff(array_keys($model->getChanges()), $model->kolomTanpaLog()));

            if ($kolom !== []) {
                LogAktivitas::catat(LogAktivitas::AKSI_UBAH, $model, $kolom);
            }
        });

        static::deleted(fn (self $model) => LogAktivitas::catat(LogAktivitas::AKSI_HAPUS, $model));
    }

    /**
     * Nama data yang tampil di log aktivitas.
     */
    public function labelLog(): string
    {
        return (string) $this->getKey();
    }

    /**
     * Kolom yang perubahannya tidak dicatat.
     *
     * @return list<string>
     */
    public function kolomTanpaLog(): array
    {
        return ['created_at', 'updated_at', 'remember_token'];
    }
}
