<?php

namespace App\Http\Requests;

use App\Enums\JenisKelamin;
use App\Enums\StatusAsuh;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AnakPantiRequest extends FormRequest
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
            'NIK' => [$required, 'string', 'max:20', Rule::unique('anak_panti', 'NIK')->ignore($this->route('anak_panti'), 'NIK')],
            'Nama' => [$required, 'string', 'max:100'],
            'Jenis_Kelamin' => [$required, Rule::enum(JenisKelamin::class)],
            'Tempat_Lahir' => [$required, 'string', 'max:50'],
            'Tanggal_Lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'Agama' => [$required, 'string', 'max:10'],
            'Status_Anak' => [$required, 'string', 'max:20'],
            'ID_Wali' => ['nullable', 'integer', Rule::exists('wali_anak', 'ID_Wali')],
            'Keterangan' => ['sometimes', 'string', 'max:50'],
            'Ukuran_Pakaian' => ['nullable', 'string', 'max:20'],
            'Ukuran_Sepatu' => ['nullable', 'string', 'max:20'],
            'Kesehatan' => ['nullable', 'string', 'max:100'],
            'Pendidikan' => [$required, 'string', 'max:100'],
            'Status_Asuh' => ['sometimes', Rule::enum(StatusAsuh::class)],
        ];
    }
}
