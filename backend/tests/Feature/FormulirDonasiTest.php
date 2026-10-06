<?php

namespace Tests\Feature;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Enums\TampilanDonatur;
use App\Models\Donasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormulirDonasiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_donation_is_recorded_as_waiting(): void
    {
        $this->travelTo('2026-10-05');

        $this->get(route('donasi.formulir'))->assertOk();

        $this->post(route('donasi.formulir.store'), $this->payload())
            ->assertRedirect(route('donasi.rekening'))
            ->assertSessionHas('status');

        $donasi = Donasi::sole();
        $this->assertSame(ProgramDonasi::Wakaf, $donasi->program);
        $this->assertSame(50000, $donasi->nominal);
        $this->assertSame('085100767634', $donasi->no_whatsapp);
        $this->assertSame(TampilanDonatur::HambaAllah, $donasi->tampil_sebagai);
        $this->assertSame('Hamba Allah', $donasi->namaTampil());
        $this->assertSame(StatusDonasi::Menunggu, $donasi->status);
        $this->assertSame('2026-10-05', $donasi->tanggal_donasi->toDateString());

        $this->get(route('donasi.rekening'))->assertSee('Rp50.000');
    }

    public function test_status_cannot_be_set_from_the_public_form(): void
    {
        $this->post(route('donasi.formulir.store'), $this->payload(['status' => StatusDonasi::Diterima->value]));

        $this->assertSame(StatusDonasi::Menunggu, Donasi::sole()->status);
    }

    public function test_invalid_donation_is_rejected(): void
    {
        $this->post(route('donasi.formulir.store'), $this->payload([
            'nominal' => '5.000',
            'tampil_sebagai' => 'Rahasia',
            'no_whatsapp' => '12345',
        ]))->assertSessionHasErrors(['nominal', 'tampil_sebagai', 'no_whatsapp']);

        $this->assertDatabaseCount('donasi', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'program' => ProgramDonasi::Wakaf->value,
            'nominal' => 'Rp50.000',
            'nama_donatur' => 'Ibu Ratna',
            'tampil_sebagai' => TampilanDonatur::HambaAllah->value,
            'alamat' => 'Jl. Soekarno Hatta, Malang',
            'no_whatsapp' => '85100767634',
            'email' => 'ratna@example.com',
            'metode_pembayaran' => MetodePembayaran::Bca->value,
            'keterangan' => 'Semoga bermanfaat',
            ...$overrides,
        ];
    }
}
