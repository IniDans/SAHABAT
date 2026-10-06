<?php

namespace App\Providers;

use App\Enums\StatusPesan;
use App\Models\Pesan;
use App\Models\ProfilPanti;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hanya admin yang boleh mengelola akun dan menghapus data.
        Gate::define('admin', fn (User $user) => $user->isAdmin());

        // Di production semua tautan memakai HTTPS agar cookie sesi tidak lewat koneksi biasa.
        URL::forceHttps($this->app->isProduction());

        // Aturan password untuk akun baru, ganti password, dan reset password.
        Password::defaults(function (): Password {
            $aturan = Password::min(8)->letters()->mixedCase()->numbers();

            // Tolak password yang pernah bocor (dicek ke haveibeenpwned tanpa mengirim password utuh).
            return $this->app->isProduction() ? $aturan->uncompromised() : $aturan;
        });

        ResetPassword::toMailUsing(fn (User $user, string $token): MailMessage => (new MailMessage)
            ->subject('Atur ulang password admin YASIBU')
            ->greeting("Halo, {$user->name}")
            ->line('Kami menerima permintaan untuk mengatur ulang password akun panel admin Anda.')
            ->action('Atur ulang password', route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]))
            ->line('Tautan ini berlaku '.config('auth.passwords.users.expire').' menit.')
            ->line('Jika Anda tidak meminta reset password, abaikan email ini. Password Anda tidak berubah.')
            ->salutation('Salam, Panti Asuhan YASIBU'));

        // Jumlah pesan belum dibaca untuk badge di sidebar dan topbar admin.
        View::composer('components.layouts.admin', function ($view): void {
            $view->with('jumlahPesanBaru', Pesan::where('status', StatusPesan::BelumDibaca)->count());
        });

        // Alamat dan media sosial di footer website diatur dari menu Profil panti.
        View::composer('components.site.footer', function ($view): void {
            $view->with('profil', ProfilPanti::semua());
        });
    }
}
