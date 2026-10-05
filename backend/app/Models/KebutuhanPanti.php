<?php

namespace App\Models;

use App\Enums\PrioritasKebutuhan;
use Database\Factories\KebutuhanPantiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('kebutuhan_panti')]
#[Fillable(['nama', 'jumlah', 'satuan', 'prioritas', 'skor_prioritas', 'terpenuhi', 'keterangan'])]
class KebutuhanPanti extends Model
{
    /** @use HasFactory<KebutuhanPantiFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
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
}
