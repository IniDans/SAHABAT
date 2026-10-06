<?php

namespace App\Http\Requests\Admin;

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
        return [
            'NIK' => ['required', 'string', 'regex:/^[0-9]{1,20}$/', Rule::unique('pengasuh', 'NIK')->ignore($this->route('pengasuh'), 'NIK')],
            'Nama' => ['required', 'string', 'max:100'],
            'Jabatan' => ['required', 'string', 'max:50'],
            'Jenis_Kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'Tempat_Lahir' => ['required', 'string', 'max:100'],
            'Tanggal_Lahir' => ['nullable', 'date', 'before_or_equal:today'],
            'Agama' => ['required', 'string', 'max:10'],
            'Email' => ['required', 'email', 'max:50'],
            'Pendidikan' => ['required', 'string', 'max:100'],
            'Kesehatan' => ['nullable', 'string', 'max:100'],
            'Nomor_Telepon' => ['required', 'string', 'regex:/^\+?[0-9][0-9 \-]{7,18}$/'],
            'Alamat' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'Nama' => 'nama',
            'Jabatan' => 'jabatan',
            'Jenis_Kelamin' => 'jenis kelamin',
            'Tempat_Lahir' => 'tempat lahir',
            'Tanggal_Lahir' => 'tanggal lahir',
            'Agama' => 'agama',
            'Email' => 'email',
            'Pendidikan' => 'pendidikan',
            'Kesehatan' => 'kesehatan',
            'Nomor_Telepon' => 'nomor telepon',
            'Alamat' => 'alamat',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'NIK.regex' => 'NIK hanya boleh berisi angka, maksimal 20 digit.',
            'Nomor_Telepon.regex' => 'Nomor telepon tidak valid. Contoh: 081234567890.',
        ];
    }
}
