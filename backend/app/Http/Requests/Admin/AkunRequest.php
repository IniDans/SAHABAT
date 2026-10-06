<?php

namespace App\Http\Requests\Admin;

use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Tambah/ubah akun panel admin. Saat mengubah, password boleh dikosongkan bila tidak diganti.
 */
class AkunRequest extends FormRequest
{
    /**
     * Checkbox yang tidak dicentang tidak ikut terkirim, jadi isi false.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $akun = $this->route('akun');

        return [
            // Nama juga dipakai sebagai username login, jadi harus unik.
            'name' => ['required', 'string', 'max:255', Rule::unique('users', 'name')->ignore($akun)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($akun)],
            'password' => [$akun ? 'nullable' : 'required', 'confirmed', 'max:255', Password::defaults()],
            'role' => ['required', Rule::enum(Role::class)],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama',
            'is_active' => 'status aktif',
        ];
    }
}
