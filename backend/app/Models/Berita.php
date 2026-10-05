<?php

namespace App\Models;

use App\Enums\KategoriBerita;
use App\Enums\StatusBerita;
use Database\Factories\BeritaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Table('berita')]
#[Fillable(['judul', 'kategori', 'ringkasan', 'isi', 'status', 'tanggal_terbit'])]
class Berita extends Model
{
    /** @use HasFactory<BeritaFactory> */
    use HasFactory;

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
            'kategori' => KategoriBerita::class,
            'status' => StatusBerita::class,
            'tanggal_terbit' => 'date:Y-m-d',
        ];
    }

    /**
     * Slug dibuat otomatis dari judul dan dijaga tetap unik.
     */
    protected static function booted(): void
    {
        static::saving(function (Berita $berita) {
            if ($berita->isDirty('judul') || blank($berita->slug)) {
                $berita->slug = $berita->uniqueSlug();
            }
        });
    }

    /**
     * URL gambar berita, atau null bila belum ada gambar.
     */
    public function gambarUrl(): ?string
    {
        return $this->gambar ? Storage::disk('public')->url($this->gambar) : null;
    }

    private function uniqueSlug(): string
    {
        $base = Str::slug($this->judul) ?: 'berita';
        $slug = $base;

        for ($suffix = 2; static::where('slug', $slug)->whereKeyNot($this->getKey())->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
