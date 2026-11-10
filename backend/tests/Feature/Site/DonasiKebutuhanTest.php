<?php

namespace Tests\Feature\Site;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Enums\TampilanDonatur;
use App\Models\Donasi;
use App\Models\KebutuhanPanti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DonasiKebutuhanTest extends TestCase
{
    use RefreshDatabase;

    public function test_urgent_needs_appear_in_the_donation_purpose_dropdown(): void
    {
        $beras = KebutuhanPanti::factory()->mendesak()->create(['nama' => 'Beras', 'jumlah' => 50, 'satuan' => 'kg']);
        KebutuhanPanti::factory()->create(['nama' => 'Seragam sekolah']);
        KebutuhanPanti::factory()->mendesak()->terpenuhi()->create(['nama' => 'Susu anak']);

        $response = $this->get(route('donasi.formulir', ['kebutuhan' => $beras->id]))->assertOk();

        $response->assertSee('<optgroup label="Kebutuhan mendesak">', false)
            ->assertSee('<option value="kebutuhan-'.$beras->id.'" selected', false)
            ->assertDontSee('value="kebutuhan-'.KebutuhanPanti::where('nama', 'Susu anak')->value('id').'"', false);
    }

    public function test_donation_for_an_urgent_need_is_saved_as_sedekah_for_that_need(): void
    {
        $beras = KebutuhanPanti::factory()->mendesak()->create(['nama' => 'Beras', 'jumlah' => 50, 'satuan' => 'kg']);

        $this->post(route('donasi.formulir.store'), $this->payload(['program' => 'kebutuhan-'.$beras->id]))
            ->assertRedirect(route('donasi.rekening'))
            ->assertSessionHasNoErrors();

        $donasi = Donasi::sole();
        $this->assertSame(ProgramDonasi::Sedekah, $donasi->program);
        $this->assertTrue($donasi->kebutuhan->is($beras));
        $this->assertSame('Sedekah untuk Beras (50 kg)', $donasi->tujuan());
    }

    public function test_fulfilled_or_non_urgent_needs_are_rejected(): void
    {
        $terpenuhi = KebutuhanPanti::factory()->mendesak()->terpenuhi()->create();
        $biasa = KebutuhanPanti::factory()->create();

        foreach ([$terpenuhi->id, $biasa->id, 999] as $id) {
            $this->post(route('donasi.formulir.store'), $this->payload(['program' => 'kebutuhan-'.$id]))
                ->assertSessionHasErrors('kebutuhan_panti_id');
        }

        $this->assertDatabaseCount('donasi', 0);
    }

    public function test_need_progress_counts_only_received_donations(): void
    {
        $beras = KebutuhanPanti::factory()->mendesak()->create(['target_dana' => 1000000]);
        Donasi::factory()->diterima()->for($beras, 'kebutuhan')->create(['nominal' => 250000]);
        Donasi::factory()->for($beras, 'kebutuhan')->create(['nominal' => 500000]);

        $kebutuhan = KebutuhanPanti::denganDanaTerkumpul()->find($beras->id);

        $this->assertSame(250000, $kebutuhan->danaTerkumpul());
        $this->assertSame(25, $kebutuhan->persenTerkumpul());
    }

    public function test_received_donation_receipt_can_be_downloaded_with_its_key(): void
    {
        $diterima = Donasi::factory()->diterima()->create();
        $menunggu = Donasi::factory()->create();

        $this->get(route('donasi.kuitansi', ['kunci' => $diterima->kunci]))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('donasi.kuitansi', ['kunci' => $menunggu->kunci]))->assertNotFound();
        $this->get(route('donasi.kuitansi', ['kunci' => 'YSB-SALAH1']))->assertNotFound();
    }

    public function test_report_page_totals_received_donations_for_the_year(): void
    {
        $this->travelTo('2026-10-15');
        Donasi::factory()->diterima()->create(['nominal' => 100000, 'tanggal_donasi' => '2026-03-02', 'program' => ProgramDonasi::Wakaf]);
        Donasi::factory()->diterima()->create(['nominal' => 50000, 'tanggal_donasi' => '2026-10-01', 'program' => ProgramDonasi::Infak, 'tampil_sebagai' => TampilanDonatur::Anonim]);
        Donasi::factory()->create(['nominal' => 900000, 'tanggal_donasi' => '2026-10-01', 'status' => StatusDonasi::Ditolak]);
        Donasi::factory()->diterima()->create(['nominal' => 70000, 'tanggal_donasi' => '2025-12-01']);

        $response = $this->get(route('donasi.laporan'))->assertOk();

        $this->assertSame(2026, $response->viewData('tahun'));
        $this->assertSame(150000, $response->viewData('total'));
        $this->assertSame(2, $response->viewData('jumlahDonasi'));
        $this->assertSame(100000, $response->viewData('perBulan')[3]);
        $response->assertSee('Rp150.000')->assertSee('Anonim')->assertDontSee('Rp900.000');

        $this->get(route('donasi.laporan', ['tahun' => 2025]))->assertSee('Rp70.000');
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
            'tampil_sebagai' => TampilanDonatur::NamaAsli->value,
            'alamat' => 'Jl. Soekarno Hatta, Malang',
            'no_whatsapp' => '85100767634',
            'email' => 'ratna@example.com',
            'metode_pembayaran' => MetodePembayaran::Bca->value,
            ...$overrides,
        ];
    }
}
