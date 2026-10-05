<?php

namespace App\Http\Resources;

use App\Models\AnakPanti;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AnakPanti */
class AnakPantiResource extends JsonResource
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
            'Jenis_Kelamin' => $this->Jenis_Kelamin,
            'Tempat_Lahir' => $this->Tempat_Lahir,
            'Tanggal_Lahir' => $this->Tanggal_Lahir?->toDateString(),
            'Umur' => $this->Tanggal_Lahir?->age,
            'Agama' => $this->Agama,
            'Status_Anak' => $this->Status_Anak,
            'ID_Wali' => $this->ID_Wali,
            'Keterangan' => $this->Keterangan,
            'Ukuran_Pakaian' => $this->Ukuran_Pakaian,
            'Ukuran_Sepatu' => $this->Ukuran_Sepatu,
            'Kesehatan' => $this->Kesehatan,
            'Pendidikan' => $this->Pendidikan,
            'Status_Asuh' => $this->Status_Asuh,

            'wali' => new WaliAnakResource($this->whenLoaded('wali')),
        ];
    }
}
