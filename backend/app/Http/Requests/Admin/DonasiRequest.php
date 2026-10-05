<?php

namespace App\Http\Requests\Admin;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DonasiRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_donatur' => ['required', 'string', 'max:255'],
            'no_whatsapp' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'program' => ['required', Rule::enum(ProgramDonasi::class)],
            'nominal' => ['required', 'integer', 'min:1000'],
            'metode_pembayaran' => ['required', Rule::enum(MetodePembayaran::class)],
            'tanggal_donasi' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', Rule::enum(StatusDonasi::class)],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'nama_donatur' => 'nama donatur',
            'no_whatsapp' => 'no WhatsApp',
            'metode_pembayaran' => 'metode pembayaran',
            'tanggal_donasi' => 'tanggal donasi',
        ];
    }
}
