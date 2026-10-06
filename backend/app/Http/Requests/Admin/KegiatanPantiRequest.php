<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisKegiatan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KegiatanPantiRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_kegiatan' => ['required', 'string', 'max:150'],
            'jenis_kegiatan' => ['required', Rule::enum(JenisKegiatan::class)],
            'deskripsi' => ['nullable', 'string', 'max:5000'],
            'tanggal_kegiatan' => ['required', 'date'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_kegiatan' => 'nama kegiatan',
            'jenis_kegiatan' => 'jenis kegiatan',
            'tanggal_kegiatan' => 'tanggal kegiatan',
        ];
    }
}
