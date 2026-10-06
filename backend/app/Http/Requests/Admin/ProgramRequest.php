<?php

namespace App\Http\Requests\Admin;

use App\Models\Program;
use Illuminate\Contracts\Validation\ValidationRule;

class ProgramRequest extends TulisanRequest
{
    protected function kelasTulisan(): string
    {
        return Program::class;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...parent::rules(),
            'tampil_di_beranda' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul program',
            'isi' => 'isi program',
        ];
    }
}
