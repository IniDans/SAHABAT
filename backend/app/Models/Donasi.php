<?php

namespace App\Models;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use Database\Factories\DonasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('donasi')]
#[Fillable(['nama_donatur', 'no_whatsapp', 'email', 'program', 'nominal', 'metode_pembayaran', 'tanggal_donasi', 'status', 'keterangan'])]
class Donasi extends Model
{
    /** @use HasFactory<DonasiFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'Menunggu',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'program' => ProgramDonasi::class,
            'nominal' => 'integer',
            'metode_pembayaran' => MetodePembayaran::class,
            'tanggal_donasi' => 'date:Y-m-d',
            'status' => StatusDonasi::class,
        ];
    }

    /**
     * Donasi yang sudah diterima (dana sudah masuk).
     */
    #[Scope]
    protected function diterima(Builder $query): void
    {
        $query->where('status', StatusDonasi::Diterima);
    }

    /**
     * Nominal dalam format rupiah, mis. "Rp150.000".
     */
    public static function rupiah(int $nominal): string
    {
        return 'Rp'.number_format($nominal, 0, ',', '.');
    }
}
