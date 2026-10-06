<?php

namespace App\Http\Controllers\Admin;

use App\Enums\KategoriProgram;
use App\Enums\StatusBerita;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProgramRequest;
use App\Models\Program;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProgramController extends Controller
{
    /**
     * Daftar program. Filter: search (judul), kategori, status.
     */
    public function index(Request $request): View
    {
        $program = Program::query()
            ->when($request->string('search')->trim()->value(), fn ($q, $search) => $q->where('judul', 'like', "%{$search}%"))
            ->when($request->string('kategori')->value(), fn ($q, $kategori) => $q->where('kategori', $kategori))
            ->when($request->enum('status', StatusBerita::class), fn ($q, $status) => $q->where('status', $status))
            ->latest('updated_at')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.program.index', [
            'program' => $program,
            'jumlahDiBeranda' => Program::where('status', StatusBerita::Terbit)->where('tampil_di_beranda', true)->count(),
        ]);
    }

    /**
     * Form tulis program.
     */
    public function create(): View
    {
        return $this->form(new Program(['kategori' => KategoriProgram::Pendidikan->value]));
    }

    /**
     * Simpan program baru.
     */
    public function store(ProgramRequest $request): RedirectResponse
    {
        $program = new Program($request->safe()->except(['gambar', 'hapus_gambar']));

        if ($request->hasFile('gambar')) {
            $program->gambar = $request->file('gambar')->store(Program::GAMBAR_FOLDER, 'public');
        }

        $program->save();

        return to_route('admin.program.edit', $program)->with('status', $this->pesanSimpan($program));
    }

    /**
     * Form ubah program.
     */
    public function edit(Program $program): View
    {
        return $this->form($program);
    }

    /**
     * Simpan perubahan program. Gambar lama dihapus bila diganti atau dihapus.
     */
    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        $program->fill($request->safe()->except(['gambar', 'hapus_gambar']));

        if ($request->hasFile('gambar') || $request->boolean('hapus_gambar')) {
            $this->deleteGambar($program);
            $program->gambar = $request->file('gambar')?->store(Program::GAMBAR_FOLDER, 'public');
        }

        $program->save();

        return to_route('admin.program.edit', $program)->with('status', $this->pesanSimpan($program));
    }

    /**
     * Tampilkan atau sembunyikan program dari bagian Program di Beranda.
     */
    public function toggleBeranda(Program $program): RedirectResponse
    {
        $program->update(['tampil_di_beranda' => ! $program->tampil_di_beranda]);

        return back()->with('status', $program->tampil_di_beranda
            ? "{$program->judul} ditampilkan di Beranda."
            : "{$program->judul} tidak lagi tampil di Beranda.");
    }

    /**
     * Hapus program beserta gambarnya.
     */
    public function destroy(Program $program): RedirectResponse
    {
        $this->deleteGambar($program);
        $program->delete();

        return to_route('admin.program.index')->with('status', 'Program berhasil dihapus.');
    }

    /**
     * Unggah gambar dari tombol "Gambar" di editor, kembalikan URL-nya.
     */
    public function unggahGambar(Request $request): JsonResponse
    {
        $request->validate([
            'gambar' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('gambar')->store(Program::GAMBAR_FOLDER.'/isi', 'public');

        return response()->json(['url' => parse_url(Storage::disk('public')->url($path), PHP_URL_PATH)]);
    }

    private function form(Program $program): View
    {
        return view('admin.program.form', [
            'program' => $program,
            'daftarKategori' => Program::daftarKategori(),
            // Pilihan untuk tombol "Baca juga" di editor.
            'tulisanLain' => Program::query()
                ->terbit()
                ->whereKeyNot($program->getKey())
                ->limit(50)
                ->get(['judul', 'slug'])
                ->map(fn (Program $lain): array => ['judul' => $lain->judul, 'url' => route('program.show', $lain->slug, false)])
                ->all(),
        ]);
    }

    private function pesanSimpan(Program $program): string
    {
        return $program->status === StatusBerita::Terbit ? 'Program diterbitkan.' : 'Program disimpan sebagai draf.';
    }

    private function deleteGambar(Program $program): void
    {
        if ($program->gambar) {
            Storage::disk('public')->delete($program->gambar);
        }
    }
}
