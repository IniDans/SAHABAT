<?php

namespace App\Http\Requests\Admin;

use App\Models\ProfilPanti;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Simpan satu bagian Profil panti. Bagian diambil dari URL, mis. PUT /admin/profil-panti/kontak.
 */
class ProfilPantiRequest extends FormRequest
{
    /**
     * Daftar yang berupa baris berulang (repeater); baris yang dibiarkan kosong dibuang.
     *
     * @var list<string>
     */
    private const DAFTAR = ['legalitas', 'inti', 'bidang', 'pembina', 'whatsapp'];

    protected function prepareForValidation(): void
    {
        foreach (self::DAFTAR as $daftar) {
            if (is_array($this->input($daftar))) {
                $this->merge([$daftar => collect($this->input($daftar))
                    ->filter(fn ($baris): bool => is_array($baris) && collect($baris)->filter(fn ($isi): bool => filled($isi))->isNotEmpty())
                    ->values()
                    ->all()]);
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $orang = fn (string $daftar, string $min): array => [
            $daftar => ['array', $min, 'max:30'],
            "{$daftar}.*.nama" => ['required', 'string', 'max:100'],
            "{$daftar}.*.jabatan" => ['required', 'string', 'max:100'],
        ];

        return match ($this->route('bagian')) {
            'lembaga' => [
                'judul_legalitas' => ['required', 'string', 'max:150'],
                'legalitas' => ['array', 'max:20'],
                'legalitas.*.label' => ['required', 'string', 'max:100'],
                'legalitas.*.nilai' => ['required', 'string', 'max:255'],
                'sejarah' => ['required', 'string', 'max:10000'],
            ],
            'visi_misi' => [
                'motto' => ['required', 'string', 'max:255'],
                'visi' => ['required', 'string', 'max:1000'],
                'misi' => ['required', 'string', 'max:5000'],
            ],
            'pengurus' => [
                ...$orang('inti', 'min:1'),
                'bidang' => ['array', 'max:20'],
                'bidang.*.nama' => ['required', 'string', 'max:150'],
                'bidang.*.anggota' => ['required', 'string', 'max:2000'],
                ...$orang('pembina', 'min:0'),
            ],
            'kontak' => [
                'email' => ['required', 'email', 'max:255'],
                'whatsapp' => ['required', 'array', 'min:1', 'max:5'],
                'whatsapp.*.nomor' => ['required', 'string', 'regex:/^\+?[0-9][0-9 \-]{7,19}$/'],
                'whatsapp.*.nama' => ['nullable', 'string', 'max:100'],
                'lokasi_nama' => ['required', 'string', 'max:150'],
                'lokasi_alamat' => ['required', 'string', 'max:500'],
                'peta' => ['nullable', 'string', 'max:255'],
                'alamat_footer' => ['required', 'string', 'max:500'],
            ],
            'sosial' => collect(ProfilPanti::MEDIA_SOSIAL)
                ->keys()
                // Hanya https:// agar tautan seperti javascript: tidak bisa disisipkan.
                ->mapWithKeys(fn (string $media): array => [$media => ['nullable', 'url:https', 'max:255']])
                ->all(),
        };
    }

    /**
     * Isi bagian yang disimpan: teks "satu per baris" diubah menjadi daftar.
     *
     * @return array<string, mixed>
     */
    public function nilai(): array
    {
        $data = $this->validated();
        $perBaris = fn (string $teks): array => collect(preg_split('/\R/', $teks))->map(fn (string $baris): string => trim($baris))->filter()->values()->all();

        return match ($this->route('bagian')) {
            'lembaga' => [...$data, 'legalitas' => $data['legalitas'] ?? []],
            'visi_misi' => [...$data, 'misi' => $perBaris($data['misi'])],
            'pengurus' => [
                'inti' => $data['inti'] ?? [],
                'bidang' => collect($data['bidang'] ?? [])
                    ->map(fn (array $bidang): array => ['nama' => $bidang['nama'], 'anggota' => $perBaris($bidang['anggota'])])
                    ->all(),
                'pembina' => $data['pembina'] ?? [],
            ],
            'kontak' => [
                ...$data,
                'whatsapp' => collect($data['whatsapp'])
                    ->map(fn (array $kontak): array => [
                        // wa.me butuh format internasional: 0812... disimpan sebagai +62812...
                        'nomor' => preg_replace('/^0/', '+62', preg_replace('/[\s\-]/', '', $kontak['nomor'])),
                        'nama' => $kontak['nama'] ?? null,
                    ])
                    ->all(),
            ],
            'sosial' => $data,
        };
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul_legalitas' => 'judul legalitas',
            'legalitas.*.label' => 'nama dokumen',
            'legalitas.*.nilai' => 'keterangan dokumen',
            'inti' => 'pengurus inti',
            'inti.*.nama' => 'nama pengurus',
            'inti.*.jabatan' => 'jabatan',
            'bidang.*.nama' => 'nama bidang',
            'bidang.*.anggota' => 'anggota bidang',
            'pembina.*.nama' => 'nama pembina',
            'pembina.*.jabatan' => 'jabatan pembina',
            'whatsapp' => 'nomor WhatsApp',
            'whatsapp.*.nomor' => 'nomor WhatsApp',
            'whatsapp.*.nama' => 'nama kontak',
            'lokasi_nama' => 'nama lokasi',
            'lokasi_alamat' => 'alamat lokasi',
            'alamat_footer' => 'alamat di footer',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'whatsapp.*.nomor.regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, tanda hubung, dan + di depan.',
            'url' => 'Kolom :attribute harus berupa tautan yang diawali https://.',
        ];
    }
}
