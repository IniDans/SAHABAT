<?php

namespace App\Http\Requests\Admin;

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
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * "Username" memakai kolom name karena tabel users tidak punya kolom username.
     */
    public function autentikasi(): User
    {
        $login = $this->string('login')->trim()->value();
        $kolom = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'name';

        return $this->periksaKredensial($kolom, $login, 'login', 'Email/username atau password salah.');
    }
}
