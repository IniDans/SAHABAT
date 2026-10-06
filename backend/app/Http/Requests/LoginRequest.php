<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\MemeriksaKredensial;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    use MemeriksaKredensial;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function autentikasi(): User
    {
        return $this->periksaKredensial('email', $this->string('email')->trim()->value(), 'email', 'Email atau password salah.');
    }
}
