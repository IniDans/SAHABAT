<?php

namespace App\Http\Requests\Admin;

use App\Models\BeratBadan;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Input berat badan bulanan: satu kolom per anak aktif, dikunci dengan NIK. Kolom kosong menghapus catatan bulan itu.
 */
class BeratBadanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Terima koma desimal ala Indonesia, mis. "23,4".
     */
    protected function prepareForValidation(): void
    {
        $berat = $this->input('berat');

        if (is_array($berat)) {
            $this->merge([
                'berat' => array_map(fn ($nilai) => is_string($nilai) ? str_replace(',', '.', trim($nilai)) : $nilai, $berat),
            ]);
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bulan' => ['required', 'date_format:Y-m', 'before_or_equal:'.now()->format('Y-m')],
            'berat' => ['required', 'array'],
            'berat.*' => ['nullable', 'numeric', 'between:'.BeratBadan::BERAT_MIN.','.BeratBadan::BERAT_MAKS],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bulan.required' => 'Pilih bulan penimbangan.',
            'bulan.date_format' => 'Bulan penimbangan tidak valid.',
            'bulan.before_or_equal' => 'Bulan penimbangan tidak boleh setelah bulan ini.',
            'berat.required' => 'Belum ada anak aktif untuk ditimbang.',
            'berat.*.numeric' => 'Berat harus berupa angka, mis. 23,4.',
            'berat.*.between' => 'Berat harus antara '.BeratBadan::BERAT_MIN.' dan '.BeratBadan::BERAT_MAKS.' kg.',
        ];
    }
}
