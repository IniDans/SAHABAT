<?php

namespace App\Models;

use App\Models\Concerns\TercatatDiLog;
use Database\Factories\WaliAnakFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Table('wali_anak', key: 'ID_Wali', timestamps: false)]
#[Fillable(['Nama_Wali', 'Alamat_Wali'])]
class WaliAnak extends Model
{
    /** @use HasFactory<WaliAnakFactory> */
    use HasFactory, TercatatDiLog;

    public function anak(): HasMany
    {
        return $this->hasMany(AnakPanti::class, 'ID_Wali', 'ID_Wali');
    }

    public function labelLog(): string
    {
        return (string) $this->Nama_Wali;
    }
}
