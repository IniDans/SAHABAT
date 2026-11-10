<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Cadangan database sebagai file SQL (INSERT) yang dikompres gzip, disimpan di disk privat.
 * Tidak memakai mysqldump agar bisa jalan di hosting tanpa akses shell.
 * Isinya memuat data pribadi anak, jadi hanya admin yang boleh mengunduhnya.
 */
class CadanganDatabase
{
    public const FOLDER = 'cadangan';

    /**
     * Tabel sementara yang tidak perlu dicadangkan.
     *
     * @var list<string>
     */
    private const TABEL_DILEWATI = ['cache', 'cache_locks', 'sessions', 'jobs', 'job_batches', 'failed_jobs'];

    private const BARIS_PER_INSERT = 100;

    /**
     * Buat cadangan baru dan kembalikan path-nya di disk, mis. "cadangan/cadangan-20261111-020000.sql.gz".
     */
    public function buat(): string
    {
        $sementara = tempnam(sys_get_temp_dir(), 'cadangan');
        $gz = gzopen($sementara, 'wb6');

        if ($gz === false) {
            throw new RuntimeException('File cadangan sementara tidak bisa dibuat.');
        }

        try {
            $this->tulis($gz);
        } finally {
            gzclose($gz);
        }

        $path = self::FOLDER.'/cadangan-'.now()->format('Ymd-His').'.sql.gz';
        $stream = fopen($sementara, 'rb');

        try {
            Storage::put($path, $stream);
        } finally {
            fclose($stream);
            @unlink($sementara);
        }

        return $path;
    }

    /**
     * Hapus cadangan lama, sisakan $simpan file terbaru.
     */
    public function hapusLama(int $simpan): int
    {
        $lama = $this->daftar()->slice($simpan);

        Storage::delete($lama->pluck('path')->all());

        return $lama->count();
    }

    /**
     * Daftar cadangan, terbaru dulu.
     *
     * @return Collection<int, array{nama: string, path: string, ukuran: int, waktu: int}>
     */
    public function daftar(): Collection
    {
        return collect(Storage::files(self::FOLDER))
            ->filter(fn (string $path): bool => self::namaValid(basename($path)))
            ->map(fn (string $path): array => [
                'nama' => basename($path),
                'path' => $path,
                'ukuran' => Storage::size($path),
                'waktu' => Storage::lastModified($path),
            ])
            ->sortByDesc('nama')
            ->values();
    }

    public static function namaValid(string $nama): bool
    {
        return (bool) preg_match('/^cadangan-\d{8}-\d{6}\.sql\.gz$/', $nama);
    }

    /**
     * @param  resource  $gz
     */
    private function tulis($gz): void
    {
        $grammar = DB::connection()->getQueryGrammar();
        $pdo = DB::connection()->getPdo();
        $mysql = DB::connection()->getDriverName() === 'mysql';

        gzwrite($gz, '-- Cadangan database '.config('app.name').' '.now()->toDateTimeString()."\n");
        gzwrite($gz, "-- Pulihkan ke database kosong yang sudah dijalankan `php artisan migrate`.\n\n");

        if ($mysql) {
            gzwrite($gz, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");
        }

        foreach ($this->tabel() as $tabel) {
            $namaTabel = $grammar->wrapTable($tabel);
            gzwrite($gz, "-- Tabel {$tabel}\nDELETE FROM {$namaTabel};\n");

            $kolom = null;
            $baris = [];

            foreach (DB::table($tabel)->cursor() as $row) {
                $row = (array) $row;
                $kolom ??= implode(', ', array_map(fn (string $nama): string => $grammar->wrap($nama), array_keys($row)));
                $baris[] = '('.implode(', ', array_map(fn ($nilai): string => $this->nilai($nilai, $pdo), $row)).')';

                if (count($baris) === self::BARIS_PER_INSERT) {
                    gzwrite($gz, "INSERT INTO {$namaTabel} ({$kolom}) VALUES\n".implode(",\n", $baris).";\n");
                    $baris = [];
                }
            }

            if ($baris !== []) {
                gzwrite($gz, "INSERT INTO {$namaTabel} ({$kolom}) VALUES\n".implode(",\n", $baris).";\n");
            }

            gzwrite($gz, "\n");
        }

        if ($mysql) {
            gzwrite($gz, "SET FOREIGN_KEY_CHECKS=1;\n");
        }
    }

    /**
     * @return list<string>
     */
    private function tabel(): array
    {
        return collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn (string $nama): bool => str_starts_with($nama, 'sqlite_') || in_array($nama, self::TABEL_DILEWATI, true))
            ->sort()
            ->values()
            ->all();
    }

    private function nilai(mixed $nilai, \PDO $pdo): string
    {
        return match (true) {
            $nilai === null => 'NULL',
            is_bool($nilai) => $nilai ? '1' : '0',
            is_int($nilai), is_float($nilai) => (string) $nilai,
            default => $pdo->quote((string) $nilai),
        };
    }
}
