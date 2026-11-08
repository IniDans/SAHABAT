<?php

namespace Tests\Feature;

use App\Services\PembacaDokumenAnak;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use RuntimeException;
use Tests\TestCase;

class MembacaDenganGeminiTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'kunci-uji', 'services.gemini.model' => 'gemini-uji']);
        Sleep::fake();
    }

    public function test_document_is_sent_to_gemini_and_the_json_answer_is_returned(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response($this->jawaban([
            ['text' => 'menimbang isi dokumen', 'thought' => true],
            ['text' => '{"jenis_dokumen":"kk","anggota":[]}'],
        ]))]);

        $hasil = app(PembacaDokumenAnak::class)->baca('isi-gambar', 'image/jpeg');

        $this->assertSame(['jenis_dokumen' => 'kk', 'anggota' => []], $hasil);
        Http::assertSent(function (Request $request): bool {
            $skema = $request['generationConfig']['responseSchema'];

            return $request->url() === 'https://generativelanguage.googleapis.com/v1beta/models/gemini-uji:generateContent'
                && $request->hasHeader('x-goog-api-key', 'kunci-uji')
                && $request['contents'][0]['parts'][0]['inlineData'] === ['mimeType' => 'image/jpeg', 'data' => base64_encode('isi-gambar')]
                && $skema['properties']['anggota']['items']['properties']['nik'] === ['type' => 'STRING', 'nullable' => true]
                && $skema['properties']['jenis_dokumen']['enum'] === ['kk', 'akta', 'lainnya']
                && ! array_key_exists('additionalProperties', $skema);
        });
    }

    public function test_reading_without_an_api_key_is_not_active(): void
    {
        config(['services.gemini.key' => null]);
        Http::fake();

        $this->assertGagal('Pembacaan otomatis belum aktif.');
        Http::assertNothingSent();
    }

    public function test_rate_limit_asks_to_try_again_later(): void
    {
        Http::fake(['*' => Http::response(['error' => ['code' => 429]], 429)]);

        $this->assertGagal('Batas pemakaian AI sedang habis, coba lagi beberapa menit lagi.');
    }

    public function test_overloaded_model_is_retried(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['error' => ['code' => 503]], 503)
            ->push($this->jawaban([['text' => '{"jenis_dokumen":"kk"}']]))]);

        $this->assertSame(['jenis_dokumen' => 'kk'], app(PembacaDokumenAnak::class)->baca('isi-gambar', 'image/jpeg'));
        Http::assertSentCount(2);
    }

    public function test_model_that_stays_overloaded_asks_to_try_again_shortly(): void
    {
        Http::fake(['*' => Http::response(['error' => ['code' => 503]], 503)]);

        $this->assertGagal('Layanan AI sedang sibuk, coba lagi sebentar lagi.');
        Http::assertSentCount(3);
    }

    public function test_server_error_reports_that_ai_cannot_be_reached(): void
    {
        Http::fake(['*' => Http::response(['error' => ['code' => 500]], 500)]);

        $this->assertGagal('Layanan AI sedang tidak dapat dihubungi.');
    }

    public function test_blocked_answer_is_reported_as_unreadable(): void
    {
        Http::fake(['*' => Http::response($this->jawaban([], 'SAFETY'))]);

        $this->assertGagal('AI tidak dapat membaca gambar ini.');
    }

    private function assertGagal(string $pesan): void
    {
        try {
            app(PembacaDokumenAnak::class)->baca('isi-gambar', 'image/jpeg');
            $this->fail('Pembacaan seharusnya gagal.');
        } catch (RuntimeException $e) {
            $this->assertSame($pesan, $e->getMessage());
        }
    }

    /**
     * @param  list<array<string, mixed>>  $bagian
     * @return array<string, mixed>
     */
    private function jawaban(array $bagian, string $alasanSelesai = 'STOP'): array
    {
        return ['candidates' => [['content' => ['role' => 'model', 'parts' => $bagian], 'finishReason' => $alasanSelesai]]];
    }
}
