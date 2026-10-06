<?php

namespace App\Http\Requests\Admin;

use App\Models\Berita;
use Illuminate\Contracts\Validation\ValidationRule;

class BeritaRequest extends TulisanRequest
{
    protected function kelasTulisan(): string
    {
        return Berita::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'tanggal_terbit' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul artikel',
            'isi' => 'isi artikel',
            'tanggal_terbit' => 'tanggal terbit',
        ];
    }
}
