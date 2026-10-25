<?php

namespace App\Models;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Enums\TampilanDonatur;
use Database\Factories\DonasiFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Table('donasi')]
#[Fillable(['nama_donatur', 'tampil_sebagai', 'no_whatsapp', 'email', 'alamat', 'program', 'nominal', 'metode_pembayaran', 'tanggal_donasi', 'status', 'keterangan'])]
class Donasi extends Model
{
    /** @use HasFactory<DonasiFactory> */
    use HasFactory;

    public const BUKTI_FOLDER = 'bukti-transfer';

    /**
     * Huruf kunci validasi, tanpa yang mudah tertukar (0/O, 1/I).
     */
    private const HURUF_KUNCI = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'Menunggu',
        'tampil_sebagai' => 'Nama asli',
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
            'tampil_sebagai' => TampilanDonatur::class,
            'bukti_hasil' => 'array',
        ];
    }

    /**
     * Setiap donasi baru mendapat kunci validasi untuk mengunggah bukti transfer.
     */
    protected static function booted(): void
    {
        static::creating(function (Donasi $donasi) {
            $donasi->kunci ??= self::buatKunci();
        });
    }

    /**
     * Kunci acak unik, mis. "YSB-7KQ2MX".
     */
    public static function buatKunci(): string
    {
        do {
            $kunci = 'YSB-'.implode('', array_map(
                fn (): string => self::HURUF_KUNCI[random_int(0, strlen(self::HURUF_KUNCI) - 1)],
                range(1, 6),
            ));
        } while (self::where('kunci', $kunci)->exists());

        return $kunci;
    }

    /**
     * Cari donasi dari kunci yang diketik donatur (huruf kecil dan spasi diabaikan).
     */
    public static function dariKunci(string $kunci): ?self
    {
        $kunci = Str::of($kunci)->upper()->remove(' ')->value();

        return self::where('kunci', str_starts_with($kunci, 'YSB-') ? $kunci : 'YSB-'.$kunci)->first();
    }

    public function buktiPath(): ?string
    {
        return $this->bukti_transfer ? self::BUKTI_FOLDER.'/'.$this->bukti_transfer : null;
    }

    /**
     * Apakah nominal di bukti transfer sama dengan nominal donasi. Null bila bukti belum terbaca.
     */
    public function buktiCocok(): ?bool
    {
        $hasil = $this->bukti_hasil;

        if (! $hasil || isset($hasil['galat'])) {
            return null;
        }

        return ($hasil['adalah_bukti_transfer'] ?? false) && ($hasil['nominal'] ?? null) === $this->nominal;
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
     * Nama yang boleh tampil di halaman publik sesuai pilihan donatur.
     */
    public function namaTampil(): string
    {
        return match ($this->tampil_sebagai) {
            TampilanDonatur::NamaAsli => $this->nama_donatur,
            TampilanDonatur::HambaAllah => 'Hamba Allah',
            TampilanDonatur::Anonim => 'Anonim',
        };
    }

    /**
     * Nomor WhatsApp yang mudah dibaca, mis. "081234567890" jadi "+62 812-3456-7890".
     */
    public function whatsappTampil(): ?string
    {
        $angka = preg_replace('/\D/', '', (string) $this->no_whatsapp);

        if ($angka === '') {
            return null;
        }

        $lokal = preg_replace('/^(62|0)/', '', $angka);

        return '+62 '.implode('-', array_filter([substr($lokal, 0, 3), substr($lokal, 3, 4), substr($lokal, 7)]));
    }

    /**
     * Nominal dalam format rupiah, mis. "Rp150.000".
     */
    public static function rupiah(int $nominal): string
    {
        return 'Rp'.number_format($nominal, 0, ',', '.');
    }
}
