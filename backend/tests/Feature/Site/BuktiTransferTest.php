<?php

namespace Tests\Feature\Site;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Enums\TampilanDonatur;
use App\Models\Donasi;
use App\Services\PembacaBuktiTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class BuktiTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_donation_gets_a_validation_key_shown_after_submitting(): void
    {
        $this->followingRedirects()
            ->post(route('donasi.formulir.store'), [
                'program' => ProgramDonasi::Wakaf->value,
                'nominal' => 'Rp50.000',
                'nama_donatur' => 'Siti Rahma',
                'tampil_sebagai' => TampilanDonatur::NamaAsli->value,
                'no_whatsapp' => '85100767634',
                'email' => 'siti@example.com',
                'metode_pembayaran' => MetodePembayaran::Bca->value,
            ])
            ->assertSee(Donasi::sole()->kunci);

        $this->assertMatchesRegularExpression('/^YSB-[A-HJ-NP-Z2-9]{6}$/', Donasi::sole()->kunci);
    }

    public function test_donation_can_be_found_by_key_regardless_of_case(): void
    {
        $donasi = Donasi::factory()->create(['nominal' => 75000]);

        $this->get(route('donasi.validasi', ['kunci' => strtolower(substr($donasi->kunci, 4))]))
            ->assertOk()
            ->assertSee(['Rp75.000', 'Unggah foto bukti transfer']);
    }

    public function test_unknown_key_shows_not_found_message(): void
    {
        $this->get(route('donasi.validasi', ['kunci' => 'YSB-AAAAAA']))
            ->assertOk()
            ->assertSee('tidak ditemukan');
    }

    public function test_uploaded_proof_is_stored_privately_and_read_by_ai(): void
    {
        Storage::fake();
        $donasi = Donasi::factory()->create(['nominal' => 50000]);
        $this->fakeAi($this->hasilAi(['nominal' => 50000]));

        $this->post(route('donasi.validasi.bukti'), ['kunci' => $donasi->kunci, 'bukti' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertRedirect(route('donasi.validasi', ['kunci' => $donasi->kunci]))
            ->assertSessionHas('status');

        $donasi->refresh();
        Storage::assertExists($donasi->buktiPath());
        $this->assertSame(50000, $donasi->bukti_hasil['nominal']);
        $this->assertTrue($donasi->buktiCocok());
        $this->assertSame(StatusDonasi::Menunggu, $donasi->status);

        $this->get(route('donasi.validasi', ['kunci' => $donasi->kunci]))
            ->assertSee('Nominal di bukti transfer sesuai dengan donasi Anda.');
    }

    public function test_proof_with_different_amount_is_flagged(): void
    {
        Storage::fake();
        $donasi = Donasi::factory()->create(['nominal' => 50000]);
        $this->fakeAi($this->hasilAi(['nominal' => 5000]));

        $this->post(route('donasi.validasi.bukti'), ['kunci' => $donasi->kunci, 'bukti' => UploadedFile::fake()->image('bukti.png')]);

        $this->assertFalse($donasi->refresh()->buktiCocok());
        $this->get(route('donasi.validasi', ['kunci' => $donasi->kunci]))
            ->assertSee('Nominal yang terbaca di bukti (Rp5.000) berbeda dengan donasi Anda.');
    }

    public function test_proof_is_kept_for_manual_check_when_ai_is_not_configured(): void
    {
        Storage::fake();
        config(['services.gemini.key' => null]);
        $donasi = Donasi::factory()->create();

        $this->post(route('donasi.validasi.bukti'), ['kunci' => $donasi->kunci, 'bukti' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertSessionHasNoErrors();

        $donasi->refresh();
        Storage::assertExists($donasi->buktiPath());
        $this->assertSame(['galat' => 'Pembacaan otomatis belum aktif.'], $donasi->bukti_hasil);
        $this->assertNull($donasi->buktiCocok());
    }

    public function test_reuploading_replaces_the_old_proof(): void
    {
        Storage::fake();
        $donasi = Donasi::factory()->create();
        $donasi->forceFill(['bukti_transfer' => 'lama.jpg'])->save();
        Storage::put('bukti-transfer/lama.jpg', 'foto lama');
        $this->fakeAi($this->hasilAi());

        $this->post(route('donasi.validasi.bukti'), ['kunci' => $donasi->kunci, 'bukti' => UploadedFile::fake()->image('baru.jpg')]);

        Storage::assertMissing('bukti-transfer/lama.jpg');
        Storage::assertExists($donasi->refresh()->buktiPath());
    }

    public function test_proof_cannot_be_uploaded_after_donation_is_processed(): void
    {
        Storage::fake();
        $donasi = Donasi::factory()->diterima()->create();

        $this->post(route('donasi.validasi.bukti'), ['kunci' => $donasi->kunci, 'bukti' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertSessionHasErrors(['bukti' => 'Donasi ini sudah diproses pengurus, bukti tidak bisa diganti.']);

        $this->assertNull($donasi->refresh()->bukti_transfer);
        $this->assertEmpty(Storage::allFiles());
    }

    public function test_proof_must_be_an_image(): void
    {
        Storage::fake();
        $donasi = Donasi::factory()->create();

        $this->post(route('donasi.validasi.bukti'), ['kunci' => $donasi->kunci, 'bukti' => UploadedFile::fake()->create('bukti.pdf', 10, 'application/pdf')])
            ->assertSessionHasErrors(['bukti' => 'Bukti transfer harus berupa foto.']);

        $this->assertNull($donasi->refresh()->bukti_transfer);
    }

    public function test_upload_with_unknown_key_is_not_found(): void
    {
        Storage::fake();

        $this->post(route('donasi.validasi.bukti'), ['kunci' => 'YSB-AAAAAA', 'bukti' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertNotFound();

        $this->assertEmpty(Storage::allFiles());
    }

    /**
     * Ganti pemanggilan Gemini dengan hasil tetap; penyimpanan hasil tetap memakai kode asli.
     *
     * @param  array<string, mixed>  $hasil
     */
    private function fakeAi(array $hasil): void
    {
        $this->partialMock(PembacaBuktiTransfer::class, function (MockInterface $mock) use ($hasil) {
            $mock->shouldReceive('baca')->once()->andReturn($hasil);
        });
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function hasilAi(array $overrides = []): array
    {
        return [
            'adalah_bukti_transfer' => true,
            'nominal' => 50000,
            'tanggal' => '2026-10-26',
            'bank' => 'BCA',
            'nama_pengirim' => 'SITI RAHMA',
            'nama_penerima' => 'YAYASAN SIRAJUL IBAD',
            'catatan' => null,
            ...$overrides,
        ];
    }
}
