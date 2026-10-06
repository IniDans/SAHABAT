<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisKelamin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PengasuhRequest;
use App\Models\Pengasuh;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PengasuhController extends Controller
{
    /**
     * Daftar pengasuh. Filter: search (nama, NIK, jabatan), jenis_kelamin.
     */
    public function index(Request $request): View
    {
        $pengasuh = Pengasuh::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('Nama', 'like', "%{$search}%")
                    ->orWhere('NIK', 'like', "%{$search}%")
                    ->orWhere('Jabatan', 'like', "%{$search}%")
            ))
            ->when($request->enum('jenis_kelamin', JenisKelamin::class), fn ($q, $jenisKelamin) => $q->where('Jenis_Kelamin', $jenisKelamin))
            ->orderBy('Nama')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengasuh.index', ['pengasuh' => $pengasuh]);
    }

    /**
     * Form tambah pengasuh.
     */
    public function create(): View
    {
        return view('admin.pengasuh.form', ['pengasuh' => new Pengasuh]);
    }

    /**
     * Simpan pengasuh baru.
     */
    public function store(PengasuhRequest $request): RedirectResponse
    {
        Pengasuh::create($request->validated());

        return to_route('admin.pengasuh.index')->with('status', 'Pengasuh berhasil ditambahkan.');
    }

    /**
     * Form ubah pengasuh.
     */
    public function edit(Pengasuh $pengasuh): View
    {
        return view('admin.pengasuh.form', ['pengasuh' => $pengasuh]);
    }

    /**
     * Simpan perubahan pengasuh.
     */
    public function update(PengasuhRequest $request, Pengasuh $pengasuh): RedirectResponse
    {
        $pengasuh->update($request->validated());

        return to_route('admin.pengasuh.index')->with('status', 'Data pengasuh berhasil diperbarui.');
    }

    /**
     * Hapus pengasuh.
     */
    public function destroy(Pengasuh $pengasuh): RedirectResponse
    {
        $pengasuh->delete();

        return to_route('admin.pengasuh.index')->with('status', 'Pengasuh berhasil dihapus.');
    }
}
