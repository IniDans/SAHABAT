<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KegiatanPantiRequest;
use App\Http\Resources\KegiatanPantiResource;
use App\Models\KegiatanPanti;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KegiatanPantiController extends Controller
{
    /**
     * Display a listing of activities, newest first.
     * Filters: search, jenis_kegiatan, dari, sampai.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $kegiatan = KegiatanPanti::query()
            ->when($request->string('search')->value(), fn ($q, $search) => $q->where(
                fn ($q) => $q->where('nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
            ))
            ->when($request->string('jenis_kegiatan')->value(), fn ($q, $jenis) => $q->where('jenis_kegiatan', $jenis))
            ->when($request->date('dari'), fn ($q, $dari) => $q->whereDate('tanggal_kegiatan', '>=', $dari))
            ->when($request->date('sampai'), fn ($q, $sampai) => $q->whereDate('tanggal_kegiatan', '<=', $sampai))
            ->orderByDesc('tanggal_kegiatan')
            ->orderByDesc('id_kegiatan')
            ->paginate($this->perPage($request));

        return KegiatanPantiResource::collection($kegiatan);
    }

    /**
     * Store a newly created activity.
     */
    public function store(KegiatanPantiRequest $request): KegiatanPantiResource
    {
        return new KegiatanPantiResource(KegiatanPanti::create($request->validated()));
    }

    /**
     * Display the specified activity.
     */
    public function show(KegiatanPanti $kegiatanPanti): KegiatanPantiResource
    {
        return new KegiatanPantiResource($kegiatanPanti);
    }

    /**
     * Update the specified activity.
     */
    public function update(KegiatanPantiRequest $request, KegiatanPanti $kegiatanPanti): KegiatanPantiResource
    {
        $kegiatanPanti->update($request->validated());

        return new KegiatanPantiResource($kegiatanPanti);
    }

    /**
     * Remove the specified activity and its photo.
     */
    public function destroy(KegiatanPanti $kegiatanPanti): Response
    {
        $foto = $kegiatanPanti->fotoPath();

        $kegiatanPanti->delete();

        if ($foto) {
            Storage::delete($foto);
        }

        return response()->noContent();
    }

    /**
     * Upload or replace the activity photo. Only the file name is kept in nama_foto.
     */
    public function uploadFoto(Request $request, KegiatanPanti $kegiatanPanti): KegiatanPantiResource
    {
        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $old = $kegiatanPanti->fotoPath();

        $kegiatanPanti->nama_foto = basename($request->file('foto')->store(KegiatanPanti::FOTO_FOLDER));
        $kegiatanPanti->save();

        if ($old) {
            Storage::delete($old);
        }

        return new KegiatanPantiResource($kegiatanPanti);
    }

    /**
     * Stream the activity photo.
     */
    public function showFoto(KegiatanPanti $kegiatanPanti): StreamedResponse
    {
        $foto = $kegiatanPanti->fotoPath();

        abort_unless($foto && Storage::exists($foto), 404);

        return Storage::response($foto);
    }
}
