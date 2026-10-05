<?php

namespace Database\Seeders;

use App\Models\AnakPanti;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Isi tabel panti dengan data dari database/sql/demo_pantiyasibu.sql.
 *
 * Dilewati bila anak_panti sudah berisi, mis. karena file SQL tersebut
 * sudah di-import langsung, atau bila file SQL-nya tidak ada. File itu
 * berisi data pribadi anak sehingga tidak ikut di-commit.
 */
class DemoSeeder extends Seeder
{
    /**
     * Lokasi dump database panti.
     */
    public const SQL_PATH = 'database/sql/demo_pantiyasibu.sql';

    public function run(): void
    {
        if (AnakPanti::query()->exists()) {
            return;
        }

        if (! is_file(base_path(self::SQL_PATH))) {
            $this->command?->warn('Lewati DemoSeeder: '.self::SQL_PATH.' tidak ditemukan.');

            return;
        }

        $sql = file_get_contents(base_path(self::SQL_PATH));

        preg_match_all('/^INSERT INTO `(\w+)`.+?;(?=\r?$)/ms', $sql, $matches, PREG_SET_ORDER);

        // Dump menyimpan anak_panti sebelum wali_anak, padahal anak_panti merujuk wali_anak.
        $inserts = collect($matches)->sortBy(fn (array $match): bool => $match[1] === 'anak_panti');

        DB::transaction(function () use ($inserts) {
            foreach ($inserts as [$insert]) {
                DB::unprepared($insert);
            }
        });
    }
}
