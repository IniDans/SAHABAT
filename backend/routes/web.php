<?php

use App\Http\Controllers\Admin\AnakPantiController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DonasiController;
use App\Http\Controllers\Admin\GaleriController;
use App\Http\Controllers\Admin\KebutuhanPantiController;
use App\Http\Controllers\Admin\KesehatanController;
use App\Http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\PesanController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('beranda');

Route::prefix('donasi')->name('donasi.')->group(function () {
    Route::view('formulir', 'pages.donasi.formulir')->name('formulir');
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
    Route::view('profil-lembaga', 'pages.tentang.profil')->name('profil');
    Route::view('visi-misi', 'pages.tentang.visi-misi')->name('visi-misi');
    Route::view('pengurus', 'pages.tentang.pengurus')->name('pengurus');
    Route::get('galeri', [SiteController::class, 'galeri'])->name('galeri');
    Route::view('kontak', 'pages.tentang.kontak')->name('kontak');
    Route::post('kontak', [SiteController::class, 'kirimPesan'])->middleware('throttle:5,1')->name('kontak.store');
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

        Route::patch('kebutuhan-panti/{kebutuhan_panti}/terpenuhi', [KebutuhanPantiController::class, 'toggleTerpenuhi'])->name('kebutuhan-panti.terpenuhi');
        Route::resource('kebutuhan-panti', KebutuhanPantiController::class)
            ->except('show')
            ->middlewareFor('destroy', 'can:admin');

        Route::get('anak-panti/ekspor', [AnakPantiController::class, 'ekspor'])->name('anak-panti.ekspor');
        Route::resource('anak-panti', AnakPantiController::class)
            ->only(['index', 'store', 'update', 'destroy'])
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
    });
});
