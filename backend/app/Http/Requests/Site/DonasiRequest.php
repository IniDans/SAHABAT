<?php

namespace App\Http\Requests\Site;

use App\Enums\MetodePembayaran;
use App\Enums\PrioritasKebutuhan;
use App\Enums\ProgramDonasi;
use App\Enums\TampilanDonatur;
use App\Models\Donasi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonasiRequest extends FormRequest
{
    /**
     * Rapikan nominal ("Rp10.000") dan nomor WhatsApp (+62 / 62 / 8xx) sebelum divalidasi.
     * Pilihan kebutuhan mendesak ("kebutuhan-3") dicatat sebagai program Sedekah untuk kebutuhan itu.
     */
    protected function prepareForValidation(): void
    {
        $nomor = preg_replace('/\D/', '', (string) $this->input('no_whatsapp'));

        if (str_starts_with($nomor, '62')) {
            $nomor = '0'.substr($nomor, 2);
        } elseif (str_starts_with($nomor, '8')) {
            $nomor = '0'.$nomor;
        }

        $tujuan = (string) $this->input('program');
        $kebutuhanId = str_starts_with($tujuan, Donasi::AWALAN_KEBUTUHAN) ? substr($tujuan, strlen(Donasi::AWALAN_KEBUTUHAN)) : null;

        $this->merge([
            'program' => $kebutuhanId !== null ? Donasi::PROGRAM_KEBUTUHAN->value : $tujuan,
            'kebutuhan_panti_id' => $kebutuhanId,
            'nominal' => preg_replace('/\D/', '', (string) $this->input('nominal')),
            'no_whatsapp' => $nomor,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'program' => ['required', Rule::enum(ProgramDonasi::class)],
            'kebutuhan_panti_id' => ['nullable', 'integer', Rule::exists('kebutuhan_panti', 'id')
                ->where('prioritas', PrioritasKebutuhan::Mendesak->value)
                ->where('terpenuhi', 0)],
            'nominal' => ['required', 'integer', 'min:10000', 'max:1000000000'],
            'nama_donatur' => ['required', 'string', 'max:255'],
            'tampil_sebagai' => ['required', Rule::enum(TampilanDonatur::class)],
            'alamat' => ['nullable', 'string', 'max:500'],
            'no_whatsapp' => ['required', 'regex:/^08\d{7,12}$/'],
            'email' => ['required', 'email', 'max:255'],
            'metode_pembayaran' => ['required', Rule::enum(MetodePembayaran::class)],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_donatur' => 'nama',
            'tampil_sebagai' => 'nama yang ditampilkan',
            'no_whatsapp' => 'no WhatsApp',
            'metode_pembayaran' => 'metode pembayaran',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'kebutuhan_panti_id.*' => 'Kebutuhan yang dipilih sudah tidak tersedia. Silakan pilih tujuan donasi lain.',
        ];
    }
}
