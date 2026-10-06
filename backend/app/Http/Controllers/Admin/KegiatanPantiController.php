<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JenisKegiatan;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\KegiatanPantiRequest;
use App\Models\KegiatanPanti;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KegiatanPantiController extends Controller
{
    /**
     * Daftar kegiatan, terbaru dulu. Filter: search (nama, lokasi), jenis.
     */
    public function index(Request $request): View
    {
        $kegiatan = KegiatanPanti::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('nama_kegiatan', 'like', "%{$search}%")->orWhere('lokasi', 'like', "%{$search}%")
            ))
            ->when($request->enum('jenis', JenisKegiatan::class), fn ($q, $jenis) => $q->where('jenis_kegiatan', $jenis))
            ->orderByDesc('tanggal_kegiatan')
            ->orderByDesc('id_kegiatan')
            ->paginate(10)
            ->withQueryString();

        return view('admin.kegiatan.index', ['kegiatan' => $kegiatan]);
    }

    /**
     * Form tambah kegiatan.
     */
    public function create(): View
    {
        return view('admin.kegiatan.form', ['kegiatan' => new KegiatanPanti]);
    }

    /**
     * Simpan kegiatan baru beserta fotonya bila ada.
     */
    public function store(KegiatanPantiRequest $request): RedirectResponse
    {
        $kegiatan = new KegiatanPanti($request->safe()->except('foto'));
        $this->simpanFoto($kegiatan, $request->file('foto'));
        $kegiatan->save();

        return to_route('admin.kegiatan.index')->with('status', 'Kegiatan berhasil ditambahkan.');
    }

    /**
     * Form ubah kegiatan.
     */
    public function edit(KegiatanPanti $kegiatan): View
    {
        return view('admin.kegiatan.form', ['kegiatan' => $kegiatan]);
    }

    /**
     * Simpan perubahan kegiatan. Foto lama dihapus bila diganti.
     */
    public function update(KegiatanPantiRequest $request, KegiatanPanti $kegiatan): RedirectResponse
    {
        $kegiatan->fill($request->safe()->except('foto'));
        $fotoLama = $this->simpanFoto($kegiatan, $request->file('foto'));
        $kegiatan->save();

        if ($fotoLama) {
            Storage::delete($fotoLama);
        }

        return to_route('admin.kegiatan.index')->with('status', 'Kegiatan berhasil diperbarui.');
    }

    /**
     * Hapus kegiatan beserta fotonya.
     */
    public function destroy(KegiatanPanti $kegiatan): RedirectResponse
    {
        $foto = $kegiatan->fotoPath();

        $kegiatan->delete();

        if ($foto) {
            Storage::delete($foto);
        }

        return to_route('admin.kegiatan.index')->with('status', 'Kegiatan berhasil dihapus.');
    }

    /**
     * Tampilkan foto kegiatan dari disk privat.
     */
    public function foto(KegiatanPanti $kegiatan): StreamedResponse
    {
        $foto = $kegiatan->fotoPath();

        abort_unless($foto && Storage::exists($foto), 404);

        return Storage::response($foto);
    }

    /**
     * Simpan foto baru ke disk privat dengan nama acak, lalu kembalikan path foto lama yang perlu dihapus.
     */
    private function simpanFoto(KegiatanPanti $kegiatan, ?UploadedFile $foto): ?string
    {
        if (! $foto) {
            return null;
        }

        $lama = $kegiatan->fotoPath();
        $kegiatan->nama_foto = basename($foto->store(KegiatanPanti::FOTO_FOLDER));

        return $lama;
    }
}
