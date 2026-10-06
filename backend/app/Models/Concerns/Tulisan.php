<?php

namespace App\Models\Concerns;

use App\Enums\StatusBerita;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Perilaku bersama untuk tulisan yang dibuat lewat editor admin (artikel kegiatan dan program):
 * slug otomatis, kategori bawaan + kategori buatan admin, gambar utama, dan data kartu publik.
 *
 * Model pemakainya wajib punya kolom judul, slug, kategori, gambar, ringkasan, isi, status, tanggal_terbit.
 */
trait Tulisan
{
    /**
     * Kategori yang selalu tersedia walau belum dipakai tulisan mana pun.
     *
     * @return list<string>
     */
    abstract public static function kategoriBawaan(): array;

    /**
     * Slug dibuat otomatis dari judul dan dijaga tetap unik.
     */
    protected static function bootTulisan(): void
    {
        static::saving(function (self $tulisan) {
            if ($tulisan->isDirty('judul') || blank($tulisan->slug)) {
                $tulisan->slug = $tulisan->uniqueSlug();
            }
        });
    }

    /**
     * Tulisan yang sudah terbit, terbaru lebih dulu.
     */
    #[Scope]
    protected function terbit(Builder $query): void
    {
        $query->where('status', StatusBerita::Terbit)
            ->latest('tanggal_terbit')
            ->latest('id');
    }

    /**
     * Kategori bawaan ditambah kategori baru yang pernah dibuat admin, urut abjad.
     *
     * @return list<string>
     */
    public static function daftarKategori(): array
    {
        return collect(static::kategoriBawaan())
            ->merge(static::query()->distinct()->pluck('kategori'))
            ->unique(fn (string $kategori): string => mb_strtolower($kategori))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * Jumlah tulisan terbit per kategori, untuk daftar kategori di halaman publik.
     *
     * @return array<string, int>
     */
    public static function jumlahTerbitPerKategori(): array
    {
        return static::query()
            ->where('status', StatusBerita::Terbit)
            ->selectRaw('kategori, count(*) as jumlah')
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->pluck('jumlah', 'kategori')
            ->map(fn ($jumlah): int => (int) $jumlah)
            ->all();
    }

    /**
     * URL gambar utama, atau null bila belum ada gambar.
     */
    public function gambarUrl(): ?string
    {
        return $this->gambar ? Storage::disk('public')->url($this->gambar) : null;
    }

    /**
     * Data untuk komponen kartu (x-post-card) di halaman publik.
     *
     * @return array{slug: string, title: string, image: string, date: string, date_iso: string, excerpt: string}
     */
    public function kartu(): array
    {
        $tanggal = ($this->tanggal_terbit ?? $this->created_at ?? now())->locale('id');

        return [
            'slug' => $this->slug,
            'title' => $this->judul,
            'image' => $this->gambarUrl() ?? 'images/tulisan-tanpa-gambar.svg',
            'date' => $tanggal->translatedFormat('j F Y'),
            'date_iso' => $tanggal->toDateString(),
            'excerpt' => (string) ($this->ringkasan ?: Str::limit(html_entity_decode(strip_tags((string) $this->isi)), 160)),
        ];
    }

    private function uniqueSlug(): string
    {
        $base = Str::slug($this->judul) ?: Str::slug(class_basename($this));
        $slug = $base;

        for ($suffix = 2; static::where('slug', $slug)->whereKeyNot($this->getKey())->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }
}
