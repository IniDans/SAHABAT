<?php

namespace App\Http\Resources;

use App\Models\WaliAnak;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin WaliAnak */
class WaliAnakResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ID_Wali' => $this->ID_Wali,
            'Nama_Wali' => $this->Nama_Wali,
            'Alamat_Wali' => $this->Alamat_Wali,
            'jumlah_anak' => $this->whenCounted('anak'),

            'anak' => AnakPantiResource::collection($this->whenLoaded('anak')),
        ];
    }
}
