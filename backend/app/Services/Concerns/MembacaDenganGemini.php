<?php

namespace App\Services\Concerns;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Kirim satu gambar atau PDF ke Gemini (Google) dan terima jawabannya sebagai JSON sesuai skema.
 */
trait MembacaDenganGemini
{
    /**
     * @param  array<string, mixed>  $skema  JSON Schema; tipe ['string', 'null'] dibaca sebagai string yang boleh null.
     * @return array<string, mixed>
     *
     * @throws RuntimeException bila pembacaan otomatis belum aktif atau gagal.
     */
    protected function bacaBerkas(string $isi, string $mediaType, string $instruksi, array $skema, string $perintah): array
    {
        $apiKey = config('services.gemini.key');

        if (blank($apiKey)) {
            throw new RuntimeException('Pembacaan otomatis belum aktif.');
        }

        try {
            // Dibatasi 40 detik supaya admin tetap mendapat pesan bila Gemini lambat.
            // Model yang sedang sibuk (503) biasanya pulih dalam beberapa detik, jadi dicoba ulang dua kali.
            $response = Http::withHeaders(['x-goog-api-key' => $apiKey])
                ->timeout(40)
                ->retry(3, 2000, fn (Throwable $e): bool => $e instanceof RequestException && $e->response->status() === 503, throw: false)
                ->post('https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent', [
                    'systemInstruction' => ['parts' => [['text' => $instruksi]]],
                    'contents' => [[
                        'role' => 'user',
                        'parts' => [
                            ['inlineData' => ['mimeType' => $mediaType, 'data' => base64_encode($isi)]],
                            ['text' => $perintah],
                        ],
                    ]],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json',
                        'responseSchema' => $this->skemaGemini($skema),
                    ],
                ]);
        } catch (ConnectionException $e) {
            report($e);

            throw new RuntimeException('Layanan AI sedang tidak dapat dihubungi.', previous: $e);
        }

        if ($response->status() === 429) {
            throw new RuntimeException('Batas pemakaian AI sedang habis, coba lagi beberapa menit lagi.');
        }

        if ($response->status() === 503) {
            throw new RuntimeException('Layanan AI sedang sibuk, coba lagi sebentar lagi.');
        }

        if ($response->failed()) {
            report($response->toException());

            throw new RuntimeException('Layanan AI sedang tidak dapat dihubungi.');
        }

        if ($response->json('promptFeedback.blockReason') || $response->json('candidates.0.finishReason') !== 'STOP') {
            throw new RuntimeException('AI tidak dapat membaca gambar ini.');
        }

        $teks = collect($response->json('candidates.0.content.parts', []))
            ->reject(fn (array $bagian): bool => $bagian['thought'] ?? false)
            ->pluck('text')
            ->implode('');

        $hasil = json_decode($teks, true);

        if (! is_array($hasil)) {
            throw new RuntimeException('Jawaban AI tidak dapat dibaca.');
        }

        return $hasil;
    }

    /**
     * Ubah JSON Schema menjadi format responseSchema Gemini: tipe huruf besar, null lewat "nullable",
     * tanpa additionalProperties, dan urutan properti sesuai skema.
     *
     * @param  array<string, mixed>  $skema
     * @return array<string, mixed>
     */
    private function skemaGemini(array $skema): array
    {
        $tipe = (array) $skema['type'];
        $hasil = ['type' => strtoupper(current(array_diff($tipe, ['null'])))];

        if (in_array('null', $tipe, true)) {
            $hasil['nullable'] = true;
        }

        foreach (['description', 'enum', 'required'] as $kunci) {
            if (isset($skema[$kunci])) {
                $hasil[$kunci] = $skema[$kunci];
            }
        }

        if (isset($skema['properties'])) {
            $hasil['properties'] = array_map(fn (array $properti): array => $this->skemaGemini($properti), $skema['properties']);
            $hasil['propertyOrdering'] = array_keys($skema['properties']);
        }

        if (isset($skema['items'])) {
            $hasil['items'] = $this->skemaGemini($skema['items']);
        }

        return $hasil;
    }
}
