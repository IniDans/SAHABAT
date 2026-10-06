<?php

namespace App\Models;

use Database\Factories\FotoGaleriFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

#[Table('galeri')]
#[Fillable(['keterangan'])]
class FotoGaleri extends Model
{
    /** @use HasFactory<FotoGaleriFactory> */
    use HasFactory;

    /**
     * Folder foto galeri di disk public.
     */
    public const GAMBAR_FOLDER = 'galeri';

    /**
     * Ukuran maksimal satu foto, dalam kilobyte.
     */
    public const UKURAN_MAKS_KB = 5120;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
        ];
    }

    /**
     * Susunan tampil: urutan kecil di depan, foto terbaru lebih dulu bila urutannya sama.
     */
    #[Scope]
    protected function urut(Builder $query): void
    {
        $query->orderBy('urutan')->latest('id');
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->gambar);
    }

    /**
     * Teks alternatif foto untuk pembaca layar.
     */
    public function teksAlt(): string
    {
        return $this->keterangan ?: 'Kegiatan Panti Asuhan YASIBU';
    }

    /**
     * Urutan untuk foto baru supaya tampil paling depan.
     */
    public static function urutanTerdepan(): int
    {
        return (int) static::query()->min('urutan') - 1;
    }

    /**
     * Susun ulang sebagian foto (mis. satu halaman) tanpa mengubah posisi foto lain.
     *
     * Posisi yang ditempati foto-foto tersebut di susunan lengkap diisi ulang sesuai
     * urutan baru, lalu seluruh urutan dinomori ulang dari 1.
     *
     * @param  list<int>  $ids
     */
    public static function susunUlang(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            $semua = static::query()->urut()->lockForUpdate()->pluck('urutan', 'id');
            $urutanBaru = array_values($ids);
            $susunan = $semua->keys()->map(fn (int $id): int => in_array($id, $ids, true) ? array_shift($urutanBaru) : $id);

            foreach ($susunan->values() as $index => $id) {
                if ($semua[$id] !== $index + 1) {
                    static::query()->whereKey($id)->update(['urutan' => $index + 1]);
                }
            }
        });
    }
}
