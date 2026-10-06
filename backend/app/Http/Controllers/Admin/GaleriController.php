<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GaleriRequest;
use App\Models\FotoGaleri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GaleriController extends Controller
{
    /**
     * Grid foto galeri sesuai urutan tampil di website.
     */
    public function index(): View
    {
        return view('admin.galeri.index', [
            'foto' => FotoGaleri::query()->urut()->paginate(24),
        ]);
    }

    /**
     * Unggah beberapa foto sekaligus. Foto baru tampil paling depan.
     */
    public function store(GaleriRequest $request): RedirectResponse
    {
        $urutan = FotoGaleri::urutanTerdepan();

        foreach (array_reverse($request->file('foto')) as $berkas) {
            $foto = new FotoGaleri(['keterangan' => $request->validated('keterangan')]);
            $foto->gambar = $berkas->store(FotoGaleri::GAMBAR_FOLDER, 'public');
            $foto->urutan = $urutan--;
            $foto->save();
        }

        return to_route('admin.galeri.index')->with('status', count($request->file('foto')).' foto berhasil diunggah.');
    }

    /**
     * Ubah keterangan satu foto.
     */
    public function update(Request $request, FotoGaleri $galeri): RedirectResponse
    {
        $galeri->update($request->validate([
            'keterangan' => ['nullable', 'string', 'max:150'],
        ]));

        return back()->with('status', 'Keterangan foto disimpan.');
    }

    /**
     * Simpan susunan baru foto di halaman yang sedang dibuka.
     */
    public function urutkan(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'urutan' => ['required', 'array'],
            'urutan.*' => ['integer', 'distinct', Rule::exists(FotoGaleri::class, 'id')],
        ]);

        FotoGaleri::susunUlang(array_map('intval', $data['urutan']));

        return back()->with('status', 'Urutan foto disimpan.');
    }

    public function destroy(FotoGaleri $galeri): RedirectResponse
    {
        Storage::disk('public')->delete($galeri->gambar);
        $galeri->delete();

        return back()->with('status', 'Foto berhasil dihapus.');
    }

    /**
     * Hapus foto-foto yang dicentang.
     */
    public function hapusBanyak(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'foto' => ['required', 'array'],
            'foto.*' => ['integer'],
        ], [
            'foto.required' => 'Pilih foto yang akan dihapus.',
        ]);

        $foto = FotoGaleri::query()->whereKey($data['foto'])->get();

        Storage::disk('public')->delete($foto->pluck('gambar')->all());
        FotoGaleri::query()->whereKey($foto->modelKeys())->delete();

        return back()->with('status', $foto->count().' foto berhasil dihapus.');
    }
}
