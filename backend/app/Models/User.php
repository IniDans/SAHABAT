<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Default yang sama dengan migration, supaya respons setelah create langsung lengkap.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => 'pengurus',
        'is_active' => true,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine whether the user is an administrator.
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::Admin;
    }

    /**
     * Cabut semua token API dan cookie "Ingat saya" milik akun ini, mis. setelah password diganti
     * atau akun dinonaktifkan. Sesi web lain ikut keluar lewat middleware auth.session dan aktif.
     *
     * @param  int|null  $kecualiToken  Token API yang sedang dipakai dan tetap boleh berlaku.
     */
    public function cabutSemuaAkses(?int $kecualiToken = null): void
    {
        $this->tokens()
            ->when($kecualiToken, fn ($query) => $query->whereKeyNot($kecualiToken))
            ->delete();

        $this->setRememberToken(Str::random(60));
        $this->save();
    }
}
