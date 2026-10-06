<?php

namespace App\Providers;

use App\Enums\StatusPesan;
use App\Models\Pesan;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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

        // Jumlah pesan belum dibaca untuk badge di sidebar dan topbar admin.
        View::composer('components.layouts.admin', function ($view): void {
            $view->with('jumlahPesanBaru', Pesan::where('status', StatusPesan::BelumDibaca)->count());
        });
    }
}
