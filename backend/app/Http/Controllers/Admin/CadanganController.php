<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LogAktivitas;
use App\Services\CadanganDatabase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Daftar, buat, dan unduh cadangan database. Khusus admin karena berisi data pribadi anak.
 */
class CadanganController extends Controller
{
    public function index(CadanganDatabase $cadangan): View
    {
        return view('admin.cadangan.index', ['cadangan' => $cadangan->daftar()]);
    }

    public function store(CadanganDatabase $cadangan): RedirectResponse
    {
        $path = $cadangan->buat();
        $cadangan->hapusLama(14);

        LogAktivitas::catat(LogAktivitas::AKSI_TAMBAH, label: basename($path), jenis: 'Cadangan');

        return to_route('admin.cadangan.index')->with('status', 'Cadangan database berhasil dibuat.');
    }

    public function unduh(string $nama): StreamedResponse
    {
        $path = CadanganDatabase::FOLDER.'/'.$nama;

        abort_unless(CadanganDatabase::namaValid($nama) && Storage::exists($path), 404);

        LogAktivitas::catat(LogAktivitas::AKSI_UNDUH, label: $nama, jenis: 'Cadangan');

        return Storage::download($path, $nama, ['Content-Type' => 'application/gzip']);
    }
}
