<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * Simpan anak hasil scan KK/akta. Tiap baris anak[i] memakai aturan form tambah anak;
 * hanya baris yang dicentang (anak[i][pilih]) yang divalidasi dan disimpan.
 * Field wali: kosong (tanpa wali), "baru" (buat dari Nama_Wali dan Alamat_Wali), atau ID wali yang ada.
 */
class SimpanPindaianRequest extends AnakPantiRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'anak' => array_filter((array) $this->input('anak', []), fn ($baris): bool => is_array($baris) && filled($baris['pilih'] ?? null)),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $kolom = Arr::except(parent::rules(), 'ID_Wali');
        $kolom['NIK'][] = 'distinct';

        return [
            'anak' => ['required', 'array'],
            ...$this->untukSetiapAnak($kolom),
            'wali' => ['nullable', Rule::when($this->input('wali') !== 'baru', ['integer', Rule::exists('wali_anak', 'ID_Wali')])],
            'Nama_Wali' => ['exclude_unless:wali,baru', 'required', 'string', 'max:100'],
            'Alamat_Wali' => ['exclude_unless:wali,baru', 'required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $pesan = parent::messages();
        $umum = array_filter($pesan, fn (string $kunci): bool => ! str_contains($kunci, '.'), ARRAY_FILTER_USE_KEY);

        return [
            ...$umum,
            ...$this->untukSetiapAnak(array_diff_key($pesan, $umum)),
            'anak.required' => 'Centang minimal satu anak yang akan disimpan.',
            'anak.*.NIK.distinct' => 'NIK ini tercantum lebih dari sekali.',
            'wali.integer' => 'Wali yang dipilih tidak ditemukan.',
            'wali.exists' => 'Wali yang dipilih tidak ditemukan.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...$this->untukSetiapAnak(parent::attributes()),
            'Nama_Wali' => 'nama wali',
            'Alamat_Wali' => 'alamat wali',
        ];
    }

    /**
     * Awali kunci dengan "anak.*." agar berlaku untuk setiap baris anak.
     *
     * @template T
     *
     * @param  array<string, T>  $daftar
     * @return array<string, T>
     */
    private function untukSetiapAnak(array $daftar): array
    {
        return Arr::mapWithKeys($daftar, fn ($nilai, string $kunci): array => ["anak.*.{$kunci}" => $nilai]);
    }
}
