<?php

namespace App\Http\Requests\Admin;

use App\Enums\KategoriKebutuhan;
use App\Enums\PrioritasKebutuhan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KebutuhanPantiRequest extends FormRequest
{
    /**
     * Checkbox yang tidak dicentang tidak ikut terkirim, jadi isi false.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['terpenuhi' => $this->boolean('terpenuhi')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama' => ['required', 'string', 'max:255'],
            'kategori' => ['required', Rule::enum(KategoriKebutuhan::class)],
            'jumlah' => ['nullable', 'integer', 'min:1'],
            'satuan' => ['nullable', 'string', 'max:30'],
            'prioritas' => ['required', Rule::enum(PrioritasKebutuhan::class)],
            'skor_prioritas' => ['required', 'integer', 'between:0,100'],
            'terpenuhi' => ['boolean'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'skor_prioritas' => 'skor prioritas',
        ];
    }
}
