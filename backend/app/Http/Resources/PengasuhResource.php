<?php

namespace App\Http\Resources;

use App\Models\Pengasuh;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Pengasuh */
class PengasuhResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'NIK' => $this->NIK,
            'Nama' => $this->Nama,
            'Jabatan' => $this->Jabatan,
            'Jenis_Kelamin' => $this->Jenis_Kelamin,
            'Tempat_Lahir' => $this->Tempat_Lahir,
            'Tanggal_Lahir' => $this->Tanggal_Lahir?->toDateString(),
            'Agama' => $this->Agama,
            'Email' => $this->Email,
            'Pendidikan' => $this->Pendidikan,
            'Kesehatan' => $this->Kesehatan,
            'Nomor_Telepon' => $this->Nomor_Telepon,
            'Alamat' => $this->Alamat,
        ];
    }
}
