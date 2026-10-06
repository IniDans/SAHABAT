<?php

use App\Http\Controllers\Admin\AkunController;
use App\Http\Controllers\Admin\AkunSayaController;
use App\Http\Controllers\Admin\AnakPantiController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonasiController;
use App\Http\Controllers\Admin\GaleriController;
use App\Http\Controllers\Admin\KebutuhanPantiController;
use App\Http\Controllers\Admin\KegiatanPantiController;
use App\Http\Controllers\Admin\KesehatanController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\LupaPasswordController;
use App\Http\Controllers\Admin\PengasuhController;
use App\Http\Controllers\Admin\PesanController;
use App\Http\Controllers\Admin\ProfilPantiController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\WaliAnakController;
use App\Models\ProfilPanti;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Login dan panel admin
|--------------------------------------------------------------------------
|
| Tanpa ADMIN_DOMAIN: /login dan /admin/... di domain website.
| Dengan ADMIN_DOMAIN (mis. admin.yasibu.org): /login dan dashboard di "/"
| hanya pada subdomain tersebut.
|
*/

// Login admin sengaja tidak ditautkan dari halaman mana pun; buka langsung lewat /login.
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');

    Route::get('lupa-password', [LupaPasswordController::class, 'create'])->name('password.request');
    Route::post('lupa-password', [LupaPasswordController::class, 'store'])->middleware('throttle:3,1')->name('password.email');
    Route::get('reset-password/{token}', [LupaPasswordController::class, 'edit'])->name('password.reset');
    Route::post('reset-password', [LupaPasswordController::class, 'update'])->middleware('throttle:6,1')->name('password.update');
});

Route::middleware(['auth', 'auth.session', 'aktif'])->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix(config('app.admin_domain') ? '' : 'admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::post('berita/gambar', [BeritaController::class, 'unggahGambar'])->middleware('throttle:30,1')->name('berita.gambar');
        // Menghapus data hanya boleh dilakukan admin, sama seperti di API.
        Route::resource('berita', BeritaController::class)
            ->except('show')
            ->parameters(['berita' => 'berita'])
            ->middlewareFor('destroy', 'can:admin');

        Route::post('program/gambar', [ProgramController::class, 'unggahGambar'])->middleware('throttle:30,1')->name('program.gambar');
        Route::patch('program/{program}/beranda', [ProgramController::class, 'toggleBeranda'])->name('program.beranda');
        Route::resource('program', ProgramController::class)
            ->except('show')
            ->parameters(['program' => 'program'])
            ->middlewareFor('destroy', 'can:admin');

        Route::patch('galeri/urutan', [GaleriController::class, 'urutkan'])->name('galeri.urutan');
        Route::delete('galeri', [GaleriController::class, 'hapusBanyak'])->middleware('can:admin')->name('galeri.hapus-banyak');
        Route::resource('galeri', GaleriController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->parameters(['galeri' => 'galeri'])
            ->middlewareFor('destroy', 'can:admin');

        Route::get('kegiatan/{kegiatan}/foto', [KegiatanPantiController::class, 'foto'])->name('kegiatan.foto');
        Route::resource('kegiatan', KegiatanPantiController::class)
            ->except('show')
            ->parameters(['kegiatan' => 'kegiatan'])
            ->middlewareFor('destroy', 'can:admin');

        Route::patch('kebutuhan-panti/{kebutuhan_panti}/terpenuhi', [KebutuhanPantiController::class, 'toggleTerpenuhi'])->name('kebutuhan-panti.terpenuhi');
        Route::resource('kebutuhan-panti', KebutuhanPantiController::class)
            ->except('show')
            ->middlewareFor('destroy', 'can:admin');

        Route::get('anak-panti/ekspor', [AnakPantiController::class, 'ekspor'])->name('anak-panti.ekspor');
        Route::resource('anak-panti', AnakPantiController::class)
            ->only(['index', 'store', 'update', 'destroy'])
            ->middlewareFor('destroy', 'can:admin');

        Route::resource('wali-anak', WaliAnakController::class)
            ->except('show')
            ->middlewareFor('destroy', 'can:admin');

        Route::resource('pengasuh', PengasuhController::class)
            ->except('show')
            ->parameters(['pengasuh' => 'pengasuh'])
            ->middlewareFor('destroy', 'can:admin');

        Route::get('kesehatan', [KesehatanController::class, 'index'])->name('kesehatan.index');
        Route::get('kesehatan/timbang', [KesehatanController::class, 'timbang'])->name('kesehatan.timbang');
        Route::put('kesehatan/timbang', [KesehatanController::class, 'simpan'])->name('kesehatan.simpan');

        Route::get('donasi/ekspor', [DonasiController::class, 'ekspor'])->name('donasi.ekspor');
        Route::patch('donasi/{donasi}/status', [DonasiController::class, 'updateStatus'])->name('donasi.status');
        Route::resource('donasi', DonasiController::class)
            ->except('show')
            ->parameters(['donasi' => 'donasi'])
            ->middlewareFor('destroy', 'can:admin');

        Route::post('pesan/tandai-dibaca', [PesanController::class, 'tandaiSemuaDibaca'])->name('pesan.tandai-dibaca');
        Route::patch('pesan/{pesan}/status', [PesanController::class, 'updateStatus'])->name('pesan.status');
        Route::post('pesan/{pesan}/balas', [PesanController::class, 'balas'])->name('pesan.balas');
        Route::resource('pesan', PesanController::class)
            ->only(['index', 'show', 'destroy'])
            ->parameters(['pesan' => 'pesan'])
            ->middlewareFor('destroy', 'can:admin');

        Route::get('profil-panti', [ProfilPantiController::class, 'edit'])->name('profil.edit');
        Route::put('profil-panti/{bagian}', [ProfilPantiController::class, 'update'])
            ->whereIn('bagian', array_keys(ProfilPanti::BAGIAN))
            ->name('profil.update');

        Route::get('akun-saya', [AkunSayaController::class, 'edit'])->name('akun-saya.edit');
        Route::put('akun-saya', [AkunSayaController::class, 'update'])->name('akun-saya.update');
        Route::put('akun-saya/password', [AkunSayaController::class, 'updatePassword'])->middleware('throttle:6,1')->name('akun-saya.password');

        // Kelola akun khusus admin.
        Route::resource('akun', AkunController::class)
            ->except('show')
            ->parameters(['akun' => 'akun'])
            ->middleware('can:admin');
    });
});
