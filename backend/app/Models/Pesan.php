<?php

namespace App\Models;

use App\Enums\StatusPesan;
use Database\Factories\PesanFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Table('pesan')]
#[Fillable(['nama', 'email', 'subjek', 'isi', 'status'])]
class Pesan extends Model
{
    /** @use HasFactory<PesanFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'Belum dibaca',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StatusPesan::class,
        ];
    }

    /**
     * Tautan mailto: untuk membalas pesan ini, lengkap dengan subjek dan kutipan pesan aslinya.
     */
    public function mailtoBalasan(): string
    {
        $kutipan = str($this->isi)->limit(1000)->explode("\n")->map(fn (string $baris): string => '> '.rtrim($baris))->implode("\n");
        $tanggal = $this->created_at->locale('id')->translatedFormat('j F Y H.i');

        return 'mailto:'.rawurlencode($this->email).'?'.http_build_query([
            'subject' => 'Re: '.($this->subjek ?: 'Pesan untuk Panti Asuhan YASIBU'),
            'body' => "Assalamu'alaikum {$this->nama},\n\n\n\nPada {$tanggal}, {$this->nama} menulis:\n{$kutipan}",
        ], encoding_type: PHP_QUERY_RFC3986);
    }
}
