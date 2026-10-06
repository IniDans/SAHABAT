<?php

namespace App\Http\Requests\Admin;

use App\Enums\JenisKelamin;
use App\Enums\StatusAsuh;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Form tambah dan edit data anak di panel admin. Nama field sama dengan kolom anak_panti.
 */
class AnakPantiRequest extends FormRequest
{
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
            'NIK' => ['required', 'string', 'regex:/^\d+$/', 'max:20', Rule::unique('anak_panti', 'NIK')->ignore($this->route('anak_panti'), 'NIK')],
            'Nama' => ['required', 'string', 'max:100'],
            'Jenis_Kelamin' => ['required', Rule::enum(JenisKelamin::class)],
            'Tempat_Lahir' => ['required', 'string', 'max:50'],
            'Tanggal_Lahir' => ['required', 'date', 'before_or_equal:today'],
            'Agama' => ['required', 'string', 'max:10'],
            'Keterangan' => ['required', 'string', 'max:50'],
            'Status_Anak' => ['required', 'string', 'max:20'],
            'Pendidikan' => ['required', 'string', 'max:100'],
            'Status_Asuh' => ['required', Rule::enum(StatusAsuh::class)],
            'Kesehatan' => ['nullable', 'string', 'max:100'],
            'Ukuran_Pakaian' => ['nullable', 'string', 'max:20'],
            'Ukuran_Sepatu' => ['nullable', 'string', 'max:20'],
            'ID_Wali' => ['nullable', 'integer', Rule::exists('wali_anak', 'ID_Wali')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => ':Attribute wajib diisi.',
            'max' => ':Attribute maksimal :max karakter.',
            'NIK.regex' => 'NIK hanya boleh berisi angka.',
            'NIK.unique' => 'NIK ini sudah terdaftar.',
            'Tanggal_Lahir.date' => 'Tanggal lahir tidak valid.',
            'Tanggal_Lahir.before_or_equal' => 'Tanggal lahir tidak boleh setelah hari ini.',
            'enum' => 'Pilihan :attribute tidak valid.',
            'ID_Wali.exists' => 'Wali yang dipilih tidak ditemukan.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'NIK' => 'NIK',
            'Nama' => 'nama lengkap',
            'Jenis_Kelamin' => 'jenis kelamin',
            'Tempat_Lahir' => 'tempat lahir',
            'Tanggal_Lahir' => 'tanggal lahir',
            'Agama' => 'agama',
            'Keterangan' => 'status anak',
            'Status_Anak' => 'kategori',
            'Pendidikan' => 'pendidikan',
            'Status_Asuh' => 'status asuh',
            'Kesehatan' => 'kesehatan',
            'Ukuran_Pakaian' => 'ukuran pakaian',
            'Ukuran_Sepatu' => 'ukuran sepatu',
        ];
    }
}
