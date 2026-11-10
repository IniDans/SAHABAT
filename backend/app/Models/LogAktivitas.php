<?php

namespace App\Models;

use Database\Factories\LogAktivitasFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Catatan aktivitas pengguna panel admin dan API. Hanya nama kolom yang berubah yang disimpan,
 * bukan nilainya, supaya data pribadi tidak tersalin ke log.
 */
#[Table('log_aktivitas', timestamps: false)]
#[Fillable(['user_id', 'nama_pengguna', 'aksi', 'subjek_tipe', 'subjek_id', 'subjek_label', 'kolom', 'ip', 'created_at'])]
class LogAktivitas extends Model
{
    /** @use HasFactory<LogAktivitasFactory> */
    use HasFactory;

    public const AKSI_TAMBAH = 'Menambah';

    public const AKSI_UBAH = 'Mengubah';

    public const AKSI_HAPUS = 'Menghapus';

    public const AKSI_MASUK = 'Masuk';

    public const AKSI_UNDUH = 'Mengunduh';

    /**
     * Nama yang mudah dibaca untuk tiap jenis data: nama kelas model => label.
     *
     * @var array<string, string>
     */
    public const JENIS_DATA = [
        'AnakPanti' => 'Anak panti',
        'BeratBadan' => 'Berat badan',
        'Cadangan' => 'Cadangan data',
        'Berita' => 'Artikel',
        'Donasi' => 'Donasi',
        'FotoGaleri' => 'Galeri',
        'KebutuhanPanti' => 'Kebutuhan panti',
        'KegiatanPanti' => 'Kegiatan panti',
        'Pengasuh' => 'Pengasuh',
        'ProfilPanti' => 'Profil panti',
        'Program' => 'Program',
        'User' => 'Akun',
        'WaliAnak' => 'Wali anak',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kolom' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Catat aktivitas pengguna yang sedang login. Tidak mencatat apa pun bila tidak ada yang login,
     * mis. donasi dari formulir publik atau seeder. Tanpa model subjek, jenis data bisa diisi lewat $jenis
     * (kunci JENIS_DATA), mis. untuk penyimpanan berat badan satu bulan sekaligus.
     *
     * @param  list<string>|null  $kolom
     */
    public static function catat(string $aksi, ?Model $subjek = null, ?array $kolom = null, ?string $label = null, ?string $jenis = null): ?self
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return null;
        }

        return self::create([
            'user_id' => $user->id,
            'nama_pengguna' => $user->name,
            'aksi' => $aksi,
            'subjek_tipe' => $subjek ? class_basename($subjek) : $jenis,
            'subjek_id' => $subjek ? (string) $subjek->getKey() : null,
            'subjek_label' => $label ?? ($subjek && method_exists($subjek, 'labelLog') ? $subjek->labelLog() : null),
            'kolom' => $kolom ?: null,
            'ip' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /**
     * Label jenis data, mis. "Anak panti".
     */
    public function jenisData(): ?string
    {
        return $this->subjek_tipe ? (self::JENIS_DATA[$this->subjek_tipe] ?? $this->subjek_tipe) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
