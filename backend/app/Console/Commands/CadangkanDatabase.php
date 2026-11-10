<?php

namespace App\Console\Commands;

use App\Services\CadanganDatabase;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('cadangan:buat {--simpan=14 : Jumlah cadangan terbaru yang disimpan}')]
#[Description('Cadangkan seluruh database ke disk privat (SQL gzip) dan hapus cadangan lama')]
class CadangkanDatabase extends Command
{
    public function handle(CadanganDatabase $cadangan): int
    {
        $path = $cadangan->buat();
        $terhapus = $cadangan->hapusLama(max(1, (int) $this->option('simpan')));

        $this->components->info("Cadangan tersimpan di {$path}. {$terhapus} cadangan lama dihapus.");

        return self::SUCCESS;
    }
}
