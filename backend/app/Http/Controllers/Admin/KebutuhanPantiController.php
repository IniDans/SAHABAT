<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriKebutuhan;
use App\Enums\PrioritasKebutuhan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KebutuhanPantiRequest;
use App\Models\KebutuhanPanti;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KebutuhanPantiController extends Controller
{
    /**
     * Daftar kebutuhan, diurutkan dari yang paling mendesak.
     * Filter: search (nama), prioritas, kategori, status (belum/terpenuhi).
     */
    public function index(Request $request): View
    {
        $kebutuhan = KebutuhanPanti::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where('nama', 'like', "%{$search}%"))
            ->when($request->enum('prioritas', PrioritasKebutuhan::class), fn ($q, $prioritas) => $q->where('prioritas', $prioritas))
            ->when($request->enum('kategori', KategoriKebutuhan::class), fn ($q, $kategori) => $q->where('kategori', $kategori))
            ->when($request->string('status')->value(), fn ($q, $status) => $q->where('terpenuhi', $status === 'terpenuhi'))
            ->palingMendesak()
            ->paginate(10)
            ->withQueryString();

        return view('admin.kebutuhan-panti.index', [
            'kebutuhan' => $kebutuhan,
            'ringkasan' => [
                'semua' => KebutuhanPanti::count(),
                'mendesak' => KebutuhanPanti::mendesak()->count(),
                'belum' => KebutuhanPanti::where('terpenuhi', false)->count(),
                'terpenuhi' => KebutuhanPanti::where('terpenuhi', true)->count(),
            ],
        ]);
    }

    /**
     * Tandai kebutuhan terpenuhi, atau kembalikan ke belum terpenuhi.
     */
    public function toggleTerpenuhi(KebutuhanPanti $kebutuhanPanti): RedirectResponse
    {
        $kebutuhanPanti->update(['terpenuhi' => ! $kebutuhanPanti->terpenuhi]);

        return back()->with('status', $kebutuhanPanti->terpenuhi
            ? "{$kebutuhanPanti->nama} ditandai terpenuhi."
            : "{$kebutuhanPanti->nama} dikembalikan ke belum terpenuhi.");
    }

    /**
     * Form tambah kebutuhan.
     */
    public function create(): View
    {
        return view('admin.kebutuhan-panti.form', ['kebutuhan' => new KebutuhanPanti]);
    }

    /**
     * Simpan kebutuhan baru.
     */
    public function store(KebutuhanPantiRequest $request): RedirectResponse
    {
        KebutuhanPanti::create($request->validated());

        return to_route('admin.kebutuhan-panti.index')->with('status', 'Kebutuhan berhasil ditambahkan.');
    }

    /**
     * Form ubah kebutuhan.
     */
    public function edit(KebutuhanPanti $kebutuhanPanti): View
    {
        return view('admin.kebutuhan-panti.form', ['kebutuhan' => $kebutuhanPanti]);
    }

    /**
     * Simpan perubahan kebutuhan.
     */
    public function update(KebutuhanPantiRequest $request, KebutuhanPanti $kebutuhanPanti): RedirectResponse
    {
        $kebutuhanPanti->update($request->validated());

        return to_route('admin.kebutuhan-panti.index')->with('status', 'Kebutuhan berhasil diperbarui.');
    }

    /**
     * Hapus kebutuhan.
     */
    public function destroy(KebutuhanPanti $kebutuhanPanti): RedirectResponse
    {
        $kebutuhanPanti->delete();

        return to_route('admin.kebutuhan-panti.index')->with('status', 'Kebutuhan berhasil dihapus.');
    }
}
