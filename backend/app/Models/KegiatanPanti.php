<?php

namespace App\Models;

use App\Enums\JenisKegiatan;
use Database\Factories\KegiatanPantiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('kegiatan_panti', key: 'id_kegiatan')]
#[Fillable(['nama_kegiatan', 'jenis_kegiatan', 'deskripsi', 'tanggal_kegiatan', 'lokasi'])]
class KegiatanPanti extends Model
{
    /** @use HasFactory<KegiatanPantiFactory> */
    use HasFactory;

    /**
     * Tabel ini hanya punya created_at.
     */
    public const UPDATED_AT = null;

    /**
     * Foto bawaan bila kegiatan belum punya foto sendiri.
     */
    public const FOTO_DEFAULT = 'default_kegiatan.jpg';

    /**
     * Folder foto kegiatan di disk bawaan (privat).
     */
    public const FOTO_FOLDER = 'kegiatan';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'nama_foto' => self::FOTO_DEFAULT,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jenis_kegiatan' => JenisKegiatan::class,
            'tanggal_kegiatan' => 'date:Y-m-d',
        ];
    }

    /**
     * Path foto di disk bawaan, atau null bila masih memakai foto bawaan.
     */
    public function fotoPath(): ?string
    {
        return $this->nama_foto && $this->nama_foto !== self::FOTO_DEFAULT
            ? self::FOTO_FOLDER.'/'.$this->nama_foto
            : null;
    }
}
