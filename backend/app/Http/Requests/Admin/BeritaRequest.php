<?php

namespace App\Http\Requests\Admin;

use App\Enums\StatusBerita;
use App\Models\Berita;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class BeritaRequest extends FormRequest
{
    /**
     * Rapikan input sebelum divalidasi:
     * - isi dari editor dibersihkan dari tag/atribut berbahaya,
     * - tombol "Simpan draf"/"Terbitkan" (aksi) menentukan status,
     * - kategori yang sama (beda huruf besar/kecil) disatukan ke ejaan yang sudah ada.
     */
    protected function prepareForValidation(): void
    {
        $isi = static::bersihkanIsi((string) $this->input('isi'));
        $kategori = str($this->input('kategori'))->squish()->ucfirst()->value();

        $this->merge([
            'isi' => trim(strip_tags($isi)) === '' && ! str_contains($isi, '<img') ? '' : $isi,
            'kategori' => collect(Berita::daftarKategori())->first(fn (string $ada): bool => mb_strtolower($ada) === mb_strtolower($kategori)) ?? $kategori,
            'status' => match ($this->input('aksi')) {
                'draf' => StatusBerita::Draft->value,
                'terbitkan' => StatusBerita::Terbit->value,
                default => $this->input('status'),
            },
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul' => ['required', 'string', 'max:255'],
            'kategori' => ['required', 'string', 'max:20'],
            'ringkasan' => ['nullable', 'string', 'max:160'],
            'isi' => ['required', 'string'],
            'status' => ['required', Rule::enum(StatusBerita::class)],
            'tanggal_terbit' => ['required', 'date'],
            'gambar' => ['nullable', 'image', 'max:2048'],
            'hapus_gambar' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'judul' => 'judul artikel',
            'isi' => 'isi artikel',
            'tanggal_terbit' => 'tanggal terbit',
        ];
    }

    /**
     * Sisakan hanya format yang bisa dibuat editor: judul, paragraf, tebal/miring/garis bawah,
     * daftar, kutipan, tautan, gambar, dan paragraf "Baca juga".
     */
    public static function bersihkanIsi(string $html): string
    {
        $config = (new HtmlSanitizerConfig)
            ->allowElement('p', ['data-baca-juga'])
            ->allowElement('h2')
            ->allowElement('h3')
            ->allowElement('strong')
            ->allowElement('em')
            ->allowElement('u')
            ->allowElement('s')
            ->allowElement('ul')
            ->allowElement('ol')
            ->allowElement('li')
            ->allowElement('blockquote')
            ->allowElement('br')
            ->allowElement('hr')
            ->allowElement('a', ['href'])
            ->allowElement('img', ['src', 'alt'])
            ->allowLinkSchemes(['http', 'https', 'mailto'])
            ->allowRelativeLinks()
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener')
            ->withMaxInputLength(500_000);

        return (new HtmlSanitizer($config))->sanitize($html);
    }
}
