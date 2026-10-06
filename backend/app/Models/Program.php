<?php

namespace App\Models;

use App\Enums\KategoriProgram;
use App\Enums\StatusBerita;
use App\Models\Concerns\Tulisan;
use Database\Factories\ProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('program')]
#[Fillable(['judul', 'kategori', 'ringkasan', 'isi', 'status', 'tampil_di_beranda'])]
class Program extends Model
{
    /** @use HasFactory<ProgramFactory> */
    use HasFactory, Tulisan;

    /**
     * Folder gambar program di disk public.
     */
    public const GAMBAR_FOLDER = 'program';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'Draft',
        'tampil_di_beranda' => false,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusBerita::class,
            'tampil_di_beranda' => 'boolean',
            'tanggal_terbit' => 'date:Y-m-d',
        ];
    }

    /**
     * Tanggal terbit diisi otomatis saat program pertama kali diterbitkan.
     */
    protected static function booted(): void
    {
        static::saving(function (Program $program) {
            if ($program->status === StatusBerita::Terbit && $program->tanggal_terbit === null) {
                $program->tanggal_terbit = today();
            }
        });
    }

    /**
     * @return list<string>
     */
    public static function kategoriBawaan(): array
    {
        return array_column(KategoriProgram::cases(), 'value');
    }
}
