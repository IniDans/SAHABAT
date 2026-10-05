<?php

use App\Http\Controllers\Api\AnakPantiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KegiatanPantiController;
use App\Http\Controllers\Api\PengasuhController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\WaliAnakController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SAHABAT API
|--------------------------------------------------------------------------
|
| Semua route di sini dilayani di subdomain API (API_DOMAIN) atau, jika
| kosong, di bawah prefix "/api". Hak akses:
|   - admin    : semua, termasuk kelola akun dan hapus data
|   - pengurus : lihat, tambah, dan ubah data (tidak bisa menghapus)
|
*/

Route::post('auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:6,1')
    ->name('auth.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::get('auth/me', [AuthController::class, 'me'])->name('auth.me');
    Route::put('auth/password', [AuthController::class, 'updatePassword'])->name('auth.password');

    // Data panti: anak_panti, wali_anak, pengasuh, kegiatan_panti
    Route::apiResource('anak-panti', AnakPantiController::class)
        ->parameters(['anak-panti' => 'anak_panti'])
        ->middlewareFor('destroy', 'can:admin');

    Route::apiResource('wali-anak', WaliAnakController::class)
        ->parameters(['wali-anak' => 'wali_anak'])
        ->middlewareFor('destroy', 'can:admin');

    Route::apiResource('pengasuh', PengasuhController::class)
        ->parameters(['pengasuh' => 'pengasuh'])
        ->middlewareFor('destroy', 'can:admin');

    Route::apiResource('kegiatan-panti', KegiatanPantiController::class)
        ->parameters(['kegiatan-panti' => 'kegiatan_panti'])
        ->middlewareFor('destroy', 'can:admin');
    Route::post('kegiatan-panti/{kegiatan_panti}/foto', [KegiatanPantiController::class, 'uploadFoto'])->name('kegiatan-panti.foto.store');
    Route::get('kegiatan-panti/{kegiatan_panti}/foto', [KegiatanPantiController::class, 'showFoto'])->name('kegiatan-panti.foto.show');

    // Kelola akun (khusus admin)
    Route::apiResource('users', UserController::class)->middleware('can:admin');
});
