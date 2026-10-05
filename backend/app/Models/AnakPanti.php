<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use App\Enums\StatusAsuh;
use Database\Factories\AnakPantiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('anak_panti', key: 'NIK', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable([
    'NIK', 'Nama', 'Jenis_Kelamin', 'Tempat_Lahir', 'Tanggal_Lahir', 'Agama',
    'Status_Anak', 'ID_Wali', 'Keterangan', 'Ukuran_Pakaian', 'Ukuran_Sepatu',
    'Kesehatan', 'Pendidikan', 'Status_Asuh',
])]
class AnakPanti extends Model
{
    /** @use HasFactory<AnakPantiFactory> */
    use HasFactory;

    /**
     * Default yang sama dengan database, supaya respons setelah create langsung lengkap.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'Keterangan' => 'Dhuafa',
        'Status_Asuh' => 'Masih Aktif',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Jenis_Kelamin' => JenisKelamin::class,
            'Tanggal_Lahir' => 'date:Y-m-d',
            'Status_Asuh' => StatusAsuh::class,
        ];
    }

    /**
     * Anak yang masih diasuh panti.
     */
    #[Scope]
    protected function aktif(Builder $query): void
    {
        $query->where('Status_Asuh', StatusAsuh::MasihAktif);
    }

    /**
     * Sama dengan trigger trg_cegah_hapus_anak_aktif: anak aktif tidak boleh dihapus.
     */
    public function isDeletable(): bool
    {
        return $this->Status_Asuh !== StatusAsuh::MasihAktif;
    }

    public function wali(): BelongsTo
    {
        return $this->belongsTo(WaliAnak::class, 'ID_Wali', 'ID_Wali');
    }
}
