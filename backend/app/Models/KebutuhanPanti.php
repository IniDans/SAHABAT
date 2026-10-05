<?php

namespace App\Models;

use App\Enums\KategoriKebutuhan;
use App\Enums\PrioritasKebutuhan;
use Database\Factories\KebutuhanPantiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('kebutuhan_panti')]
#[Fillable(['nama', 'kategori', 'jumlah', 'satuan', 'prioritas', 'skor_prioritas', 'terpenuhi', 'keterangan'])]
class KebutuhanPanti extends Model
{
    /** @use HasFactory<KebutuhanPantiFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'kategori' => 'Perlengkapan',
        'prioritas' => 'Sedang',
        'skor_prioritas' => 50,
        'terpenuhi' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kategori' => KategoriKebutuhan::class,
            'jumlah' => 'integer',
            'prioritas' => PrioritasKebutuhan::class,
            'skor_prioritas' => 'integer',
            'terpenuhi' => 'boolean',
        ];
    }

    /**
     * Kebutuhan berprioritas mendesak yang belum terpenuhi.
     */
    #[Scope]
    protected function mendesak(Builder $query): void
    {
        $query->where('prioritas', PrioritasKebutuhan::Mendesak)->where('terpenuhi', false);
    }

    /**
     * Urutkan dari urgensi tertinggi, lalu yang belum terpenuhi, lalu skor prioritas.
     */
    #[Scope]
    protected function palingMendesak(Builder $query): void
    {
        $query->orderByRaw("case prioritas when 'Mendesak' then 0 when 'Sedang' then 1 else 2 end")
            ->orderBy('terpenuhi')
            ->orderByDesc('skor_prioritas')
            ->orderBy('nama');
    }
}
