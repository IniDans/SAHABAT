<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriBerita;
use App\Enums\StatusBerita;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BeritaRequest;
use App\Models\Berita;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BeritaController extends Controller
{
    /**
     * Daftar berita. Filter: search (judul), kategori, status.
     */
    public function index(Request $request): View
    {
        $berita = Berita::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where('judul', 'like', "%{$search}%"))
            ->when($request->enum('kategori', KategoriBerita::class), fn ($q, $kategori) => $q->where('kategori', $kategori))
            ->when($request->enum('status', StatusBerita::class), fn ($q, $status) => $q->where('status', $status))
            ->latest('tanggal_terbit')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.berita.index', ['berita' => $berita]);
    }

    /**
     * Form tambah berita.
     */
    public function create(): View
    {
        return view('admin.berita.form', ['berita' => new Berita(['tanggal_terbit' => today()])]);
    }

    /**
     * Simpan berita baru.
     */
    public function store(BeritaRequest $request): RedirectResponse
    {
        $berita = new Berita($request->safe()->except(['gambar', 'hapus_gambar']));

        if ($request->hasFile('gambar')) {
            $berita->gambar = $request->file('gambar')->store(Berita::GAMBAR_FOLDER, 'public');
        }

        $berita->save();

        return to_route('admin.berita.index')->with('status', 'Berita berhasil ditambahkan.');
    }

    /**
     * Form ubah berita.
     */
    public function edit(Berita $berita): View
    {
        return view('admin.berita.form', ['berita' => $berita]);
    }

    /**
     * Simpan perubahan berita. Gambar lama dihapus bila diganti atau dihapus.
     */
    public function update(BeritaRequest $request, Berita $berita): RedirectResponse
    {
        $berita->fill($request->safe()->except(['gambar', 'hapus_gambar']));

        if ($request->hasFile('gambar') || $request->boolean('hapus_gambar')) {
            $this->deleteGambar($berita);
            $berita->gambar = $request->file('gambar')?->store(Berita::GAMBAR_FOLDER, 'public');
        }

        $berita->save();

        return to_route('admin.berita.index')->with('status', 'Berita berhasil diperbarui.');
    }

    /**
     * Hapus berita beserta gambarnya.
     */
    public function destroy(Berita $berita): RedirectResponse
    {
        $this->deleteGambar($berita);
        $berita->delete();

        return to_route('admin.berita.index')->with('status', 'Berita berhasil dihapus.');
    }

    private function deleteGambar(Berita $berita): void
    {
        if ($berita->gambar) {
            Storage::disk('public')->delete($berita->gambar);
        }
    }
}
