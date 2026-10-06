<?php

use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Website publik
|--------------------------------------------------------------------------
|
| Login dan panel admin ada di routes/admin.php, yang bisa dipasang di
| subdomain sendiri lewat ADMIN_DOMAIN.
|
*/

Route::get('/', [SiteController::class, 'home'])->name('beranda');

Route::prefix('donasi')->name('donasi.')->group(function () {
    Route::get('formulir', [SiteController::class, 'formulirDonasi'])->name('formulir');
    Route::post('formulir', [SiteController::class, 'kirimDonasi'])->middleware('throttle:5,1')->name('formulir.store');
    Route::view('validasi', 'pages.donasi.validasi')->name('validasi');
    Route::view('cara-qris', 'pages.donasi.cara-qris')->name('qris');
    Route::view('rekening', 'pages.donasi.rekening')->name('rekening');
});

Route::get('program', [SiteController::class, 'programIndex'])->name('program.index');
Route::get('program/{slug}', [SiteController::class, 'programShow'])->name('program.show');

Route::get('artikel', [SiteController::class, 'artikelIndex'])->name('artikel.index');
Route::get('artikel/{slug}', [SiteController::class, 'artikelShow'])->name('artikel.show');

Route::prefix('tentang-kami')->name('tentang.')->group(function () {
    Route::get('profil-lembaga', [SiteController::class, 'profil'])->name('profil');
    Route::get('visi-misi', [SiteController::class, 'visiMisi'])->name('visi-misi');
    Route::get('pengurus', [SiteController::class, 'pengurus'])->name('pengurus');
    Route::get('galeri', [SiteController::class, 'galeri'])->name('galeri');
    Route::get('kontak', [SiteController::class, 'kontak'])->name('kontak');
    Route::post('kontak', [SiteController::class, 'kirimPesan'])->middleware('throttle:5,1')->name('kontak.store');
    Route::get('anak-asuh', [SiteController::class, 'anakAsuh'])->name('anak-asuh');
});
