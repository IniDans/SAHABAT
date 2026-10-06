<?php

namespace App\Http\Requests\Concerns;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Pemeriksaan login yang dipakai bersama oleh login panel admin dan login API.
 *
 * - Akun dikunci 15 menit setelah 5 kali salah password dari IP yang sama.
 * - Hash tetap dihitung walau akun tidak ada, supaya lama respons tidak
 *   membocorkan apakah sebuah email terdaftar.
 * - Hash lama (bcrypt) langsung diganti ke algoritma sekarang (Argon2id)
 *   begitu pemiliknya berhasil login.
 */
trait MemeriksaKredensial
{
    /**
     * Jumlah percobaan gagal sebelum akun dikunci sementara.
     */
    public const MAKS_PERCOBAAN = 5;

    /**
     * Lama akun dikunci setelah terlalu banyak percobaan gagal.
     */
    public const LAMA_KUNCI_DETIK = 15 * 60;

    /**
     * Hash acak pembanding saat akun tidak ditemukan.
     */
    private static ?string $hashPengganti = null;

    /**
     * Kembalikan user bila kredensial benar dan akun aktif, atau lempar galat validasi pada $field.
     *
     * @throws ValidationException
     */
    protected function periksaKredensial(string $kolom, string $nilai, string $field, string $pesanSalah): User
    {
        $kunci = $this->kunciPembatasLogin($nilai);

        if (RateLimiter::tooManyAttempts($kunci, self::MAKS_PERCOBAAN)) {
            event(new Lockout($this));

            throw ValidationException::withMessages([
                $field => 'Terlalu banyak percobaan login. Coba lagi dalam '.ceil(RateLimiter::availableIn($kunci) / 60).' menit.',
            ]);
        }

        $password = (string) $this->input('password');
        $user = User::where($kolom, $nilai)->first();
        $cocok = Hash::check($password, $user?->password ?? self::$hashPengganti ??= Hash::make(Str::random(40)));

        if (! $user || ! $cocok) {
            RateLimiter::hit($kunci, self::LAMA_KUNCI_DETIK);
            Log::warning('Login gagal.', ['login' => $nilai, 'ip' => $this->ip()]);

            throw ValidationException::withMessages([$field => $pesanSalah]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                $field => 'Akun ini sudah dinonaktifkan. Hubungi admin.',
            ]);
        }

        RateLimiter::clear($kunci);

        if (Hash::needsRehash($user->password)) {
            $user->forceFill(['password' => $password])->save();
        }

        return $user;
    }

    private function kunciPembatasLogin(string $nilai): string
    {
        return 'login:'.Str::transliterate(Str::lower($nilai)).'|'.$this->ip();
    }
}
