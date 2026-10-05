<?php

namespace App\Models;

use App\Enums\JenisKelamin;
use Database\Factories\PengasuhFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('pengasuh', key: 'NIK', keyType: 'string', incrementing: false, timestamps: false)]
#[Fillable([
    'NIK', 'Nama', 'Jabatan', 'Jenis_Kelamin', 'Tempat_Lahir', 'Tanggal_Lahir',
    'Agama', 'Email', 'Pendidikan', 'Kesehatan', 'Nomor_Telepon', 'Alamat',
])]
class Pengasuh extends Model
{
    /** @use HasFactory<PengasuhFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'Jenis_Kelamin' => JenisKelamin::class,
            'Tanggal_Lahir' => 'date:Y-m-d',
        ];
    }
}
