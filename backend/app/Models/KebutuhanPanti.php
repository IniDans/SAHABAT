<?php

namespace App\Models;

use App\Enums\KategoriKebutuhan;
use App\Enums\PrioritasKebutuhan;
use App\Enums\StatusDonasi;
use App\Models\Concerns\TercatatDiLog;
use Database\Factories\KebutuhanPantiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('kebutuhan_panti')]
#[Fillable(['nama', 'kategori', 'jumlah', 'satuan', 'target_dana', 'prioritas', 'skor_prioritas', 'terpenuhi', 'keterangan'])]
class KebutuhanPanti extends Model
{
    /** @use HasFactory<KebutuhanPantiFactory> */
    use HasFactory, TercatatDiLog;

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
            'target_dana' => 'integer',
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

    /**
     * Sertakan total donasi diterima untuk kebutuhan ini sebagai atribut dana_terkumpul.
     */
    #[Scope]
    protected function denganDanaTerkumpul(Builder $query): void
    {
        $query->withSum(['donasi as dana_terkumpul' => fn (Builder $donasi) => $donasi->where('status', StatusDonasi::Diterima)], 'nominal');
    }

    /**
     * Total donasi diterima. Memakai hasil denganDanaTerkumpul() bila sudah dimuat.
     */
    public function danaTerkumpul(): int
    {
        if (! array_key_exists('dana_terkumpul', $this->attributes)) {
            $this->attributes['dana_terkumpul'] = $this->donasi()->where('status', StatusDonasi::Diterima)->sum('nominal');
        }

        return (int) $this->attributes['dana_terkumpul'];
    }

    /**
     * Persentase dana terkumpul terhadap target (0-100), atau null bila target belum diisi.
     */
    public function persenTerkumpul(): ?int
    {
        if (! $this->target_dana) {
            return null;
        }

        return (int) min(100, floor($this->danaTerkumpul() / $this->target_dana * 100));
    }

    /**
     * Nama kebutuhan beserta jumlahnya, mis. "Beras (50 kg)".
     */
    public function label(): string
    {
        if (! $this->jumlah) {
            return $this->nama;
        }

        return trim(sprintf('%s (%s %s)', $this->nama, number_format($this->jumlah, 0, ',', '.'), $this->satuan));
    }

    public function donasi(): HasMany
    {
        return $this->hasMany(Donasi::class);
    }

    public function labelLog(): string
    {
        return $this->nama;
    }
}
