<?php

namespace App\Http\Requests;

use App\Enums\JenisKelamin;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PengasuhRequest extends FormRequest
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
            'NIK' => [$required, 'string', 'max:20', Rule::unique('pengasuh', 'NIK')->ignore($this->route('pengasuh'), 'NIK')],
            'Nama' => [$required, 'string', 'max:100'],
            'Jabatan' => [$required, 'string', 'max:50'],
            'Jenis_Kelamin' => [$required, Rule::enum(JenisKelamin::class)],
            'Tempat_Lahir' => [$required, 'string', 'max:100'],
            'Tanggal_Lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'Agama' => [$required, 'string', 'max:10'],
            'Email' => [$required, 'string', 'max:50'],
            'Pendidikan' => [$required, 'string', 'max:100'],
            'Kesehatan' => ['nullable', 'string', 'max:100'],
            'Nomor_Telepon' => [$required, 'string', 'max:20'],
            'Alamat' => [$required, 'string', 'max:100'],
        ];
    }
}
