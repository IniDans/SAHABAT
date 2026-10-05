<?php

namespace App\Http\Resources;

use App\Models\KegiatanPanti;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin KegiatanPanti */
class KegiatanPantiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id_kegiatan' => $this->id_kegiatan,
            'nama_kegiatan' => $this->nama_kegiatan,
            'jenis_kegiatan' => $this->jenis_kegiatan,
            'deskripsi' => $this->deskripsi,
            'tanggal_kegiatan' => $this->tanggal_kegiatan?->toDateString(),
            'lokasi' => $this->lokasi,
            'nama_foto' => $this->nama_foto,
            'foto_url' => $this->fotoPath() ? route('kegiatan-panti.foto.show', $this->resource) : null,
            'created_at' => $this->created_at,
        ];
    }
}
