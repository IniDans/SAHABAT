<?php

namespace App\Http\Requests\Admin;

use App\Models\FotoGaleri;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Unggah beberapa foto galeri sekaligus dengan satu keterangan opsional.
 */
class GaleriRequest extends FormRequest
{
    /**
     * Jumlah foto maksimal dalam sekali unggah.
     */
    public const JUMLAH_MAKS = 20;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'foto' => ['required', 'array', 'max:'.self::JUMLAH_MAKS],
            'foto.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.FotoGaleri::UKURAN_MAKS_KB],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'foto.required' => 'Pilih minimal satu foto.',
            'foto.max' => 'Maksimal '.self::JUMLAH_MAKS.' foto sekali unggah.',
            'foto.*.image' => 'Berkas :position harus berupa gambar.',
            'foto.*.mimes' => 'Berkas :position harus JPG, PNG, atau WEBP.',
            'foto.*.max' => 'Berkas :position melebihi 5 MB.',
        ];
    }
}
