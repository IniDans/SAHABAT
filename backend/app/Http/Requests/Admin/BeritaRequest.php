<?php

namespace App\Http\Requests\Admin;

use App\Enums\KategoriBerita;
use App\Enums\StatusBerita;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BeritaRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::enum(KategoriBerita::class)],
            'ringkasan' => ['nullable', 'string', 'max:500'],
            'isi' => ['required', 'string'],
            'status' => ['required', Rule::enum(StatusBerita::class)],
            'tanggal_terbit' => ['required', 'date'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'hapus_gambar' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'tanggal_terbit' => 'tanggal terbit',
        ];
    }
}
