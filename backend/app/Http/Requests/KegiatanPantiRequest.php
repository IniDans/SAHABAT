<?php

namespace App\Http\Requests;

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
        $required = $this->isMethod('POST') ? 'required' : 'sometimes';

        return [
            'nama_kegiatan' => [$required, 'string', 'max:150'],
            'jenis_kegiatan' => [$required, Rule::enum(JenisKegiatan::class)],
            'deskripsi' => ['nullable', 'string'],
            'tanggal_kegiatan' => [$required, 'date'],
            'lokasi' => ['nullable', 'string', 'max:100'],
        ];
    }
}
