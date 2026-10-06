<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\WaliAnakRequest;
use App\Models\WaliAnak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WaliAnakController extends Controller
{
    /**
     * Daftar wali beserta jumlah anak yang diwalikan. Filter: search (nama, alamat).
     */
    public function index(Request $request): View
    {
        $wali = WaliAnak::query()
            ->withCount('anak')
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('Nama_Wali', 'like', "%{$search}%")->orWhere('Alamat_Wali', 'like', "%{$search}%")
            ))
            ->orderBy('Nama_Wali')
            ->paginate(15)
            ->withQueryString();

        return view('admin.wali-anak.index', ['wali' => $wali]);
    }

    /**
     * Form tambah wali.
     */
    public function create(): View
    {
        return view('admin.wali-anak.form', ['wali' => new WaliAnak]);
    }

    /**
     * Simpan wali baru.
     */
    public function store(WaliAnakRequest $request): RedirectResponse
    {
        WaliAnak::create($request->validated());

        return to_route('admin.wali-anak.index')->with('status', 'Wali berhasil ditambahkan.');
    }

    /**
     * Form ubah wali.
     */
    public function edit(WaliAnak $waliAnak): View
    {
        return view('admin.wali-anak.form', ['wali' => $waliAnak]);
    }

    /**
     * Simpan perubahan wali.
     */
    public function update(WaliAnakRequest $request, WaliAnak $waliAnak): RedirectResponse
    {
        $waliAnak->update($request->validated());

        return to_route('admin.wali-anak.index')->with('status', 'Data wali berhasil diperbarui.');
    }

    /**
     * Hapus wali. Anak yang diwalikan tetap ada; foreign key mengosongkan kolom walinya.
     */
    public function destroy(WaliAnak $waliAnak): RedirectResponse
    {
        $waliAnak->delete();

        return to_route('admin.wali-anak.index')->with('status', 'Wali berhasil dihapus.');
    }
}
