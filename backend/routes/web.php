<?php

use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonasiController;
use App\Http\Controllers\Admin\KebutuhanPantiController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('beranda');

Route::prefix('donasi')->name('donasi.')->group(function () {
    Route::view('formulir', 'pages.donasi.formulir')->name('formulir');
    Route::view('validasi', 'pages.donasi.validasi')->name('validasi');
    Route::view('cara-qris', 'pages.donasi.cara-qris')->name('qris');
    Route::view('rekening', 'pages.donasi.rekening')->name('rekening');
});

Route::get('program', [SiteController::class, 'programIndex'])->name('program.index');
Route::get('program/{slug}', [SiteController::class, 'programShow'])->name('program.show');

Route::get('artikel', [SiteController::class, 'artikelIndex'])->name('artikel.index');
Route::get('artikel/{slug}', [SiteController::class, 'artikelShow'])->name('artikel.show');

Route::prefix('tentang-kami')->name('tentang.')->group(function () {
    Route::view('profil-lembaga', 'pages.tentang.profil')->name('profil');
    Route::view('visi-misi', 'pages.tentang.visi-misi')->name('visi-misi');
    Route::view('pengurus', 'pages.tentang.pengurus')->name('pengurus');
    Route::get('galeri', [SiteController::class, 'galeri'])->name('galeri');
    Route::view('kontak', 'pages.tentang.kontak')->name('kontak');
    Route::get('anak-asuh', [SiteController::class, 'anakAsuh'])->name('anak-asuh');
});

// Login admin sengaja tidak ditautkan dari halaman mana pun; buka langsung lewat /login.
Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        // Menghapus data hanya boleh dilakukan admin, sama seperti di API.
        Route::resource('berita', BeritaController::class)
            ->except('show')
            ->parameters(['berita' => 'berita'])
            ->middlewareFor('destroy', 'can:admin');

        Route::resource('kebutuhan-panti', KebutuhanPantiController::class)
            ->except('show')
            ->middlewareFor('destroy', 'can:admin');

        Route::patch('donasi/{donasi}/status', [DonasiController::class, 'updateStatus'])->name('donasi.status');
        Route::resource('donasi', DonasiController::class)
            ->except('show')
            ->parameters(['donasi' => 'donasi'])
            ->middlewareFor('destroy', 'can:admin');
    });
});
