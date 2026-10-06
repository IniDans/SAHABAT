<?php

namespace App\Models;

use App\Enums\KategoriBerita;
use App\Enums\StatusBerita;
use App\Models\Concerns\Tulisan;
use Database\Factories\BeritaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('berita')]
#[Fillable(['judul', 'kategori', 'ringkasan', 'isi', 'status', 'tanggal_terbit'])]
class Berita extends Model
{
    /** @use HasFactory<BeritaFactory> */
    use HasFactory, Tulisan;

    /**
     * Folder gambar berita di disk public.
     */
    public const GAMBAR_FOLDER = 'berita';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'Draft',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusBerita::class,
            'tanggal_terbit' => 'date:Y-m-d',
        ];
    }

    /**
     * @return list<string>
     */
    public static function kategoriBawaan(): array
    {
        return array_column(KategoriBerita::cases(), 'value');
    }
}
