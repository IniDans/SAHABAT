<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriBerita;
use App\Enums\StatusBerita;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BeritaRequest;
use App\Models\Berita;
use Illuminate\Http\JsonResponse;
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
            ->when($request->string('kategori')->value(), fn ($q, $kategori) => $q->where('kategori', $kategori))
            ->when($request->enum('status', StatusBerita::class), fn ($q, $status) => $q->where('status', $status))
            ->latest('tanggal_terbit')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.berita.index', ['berita' => $berita]);
    }

    /**
     * Form tulis artikel.
     */
    public function create(): View
    {
        return $this->form(new Berita(['tanggal_terbit' => today(), 'kategori' => KategoriBerita::Kegiatan->value]));
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

        return to_route('admin.berita.edit', $berita)->with('status', $this->pesanSimpan($berita));
    }

    /**
     * Form ubah artikel.
     */
    public function edit(Berita $berita): View
    {
        return $this->form($berita);
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

        return to_route('admin.berita.edit', $berita)->with('status', $this->pesanSimpan($berita));
    }

    /**
     * Hapus berita beserta gambarnya.
     */
    public function destroy(Berita $berita): RedirectResponse
    {
        $this->deleteGambar($berita);
        $berita->delete();

        return to_route('admin.berita.index')->with('status', 'Artikel berhasil dihapus.');
    }

    /**
     * Unggah gambar dari tombol "Gambar" di editor, kembalikan URL-nya.
     */
    public function unggahGambar(Request $request): JsonResponse
    {
        $request->validate([
            'gambar' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('gambar')->store(Berita::GAMBAR_FOLDER.'/isi', 'public');

        return response()->json(['url' => parse_url(Storage::disk('public')->url($path), PHP_URL_PATH)]);
    }

    private function form(Berita $berita): View
    {
        return view('admin.berita.form', [
            'berita' => $berita,
            'daftarKategori' => Berita::daftarKategori(),
            // Pilihan untuk tombol "Baca juga" di editor.
            'tulisanLain' => Berita::query()
                ->terbit()
                ->whereKeyNot($berita->getKey())
                ->limit(50)
                ->get(['judul', 'slug'])
                ->map(fn (Berita $lain): array => ['judul' => $lain->judul, 'url' => route('artikel.show', $lain->slug, false)])
                ->all(),
        ]);
    }

    private function pesanSimpan(Berita $berita): string
    {
        return $berita->status === StatusBerita::Terbit ? 'Artikel diterbitkan.' : 'Artikel disimpan sebagai draf.';
    }

    private function deleteGambar(Berita $berita): void
    {
        if ($berita->gambar) {
            Storage::disk('public')->delete($berita->gambar);
        }
    }
}
