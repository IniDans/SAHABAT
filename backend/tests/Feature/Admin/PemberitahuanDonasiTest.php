<?php

namespace Tests\Feature\Admin;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Enums\TampilanDonatur;
use App\Mail\PemberitahuanDonasi;
use App\Models\Donasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PemberitahuanDonasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_donor_is_emailed_after_submitting_the_public_form(): void
    {
        $this->post(route('donasi.formulir.store'), [
            'program' => ProgramDonasi::Infak->value,
            'nominal' => 'Rp50.000',
            'nama_donatur' => 'Ibu Ratna',
            'tampil_sebagai' => TampilanDonatur::NamaAsli->value,
            'no_whatsapp' => '85100767634',
            'email' => 'ratna@example.com',
            'metode_pembayaran' => MetodePembayaran::Bca->value,
        ])->assertRedirect(route('donasi.rekening'));

        Mail::assertSent(PemberitahuanDonasi::class, fn (PemberitahuanDonasi $mail): bool => $mail->hasTo('ratna@example.com')
            && $mail->attachments() === []);
    }

    public function test_receipt_is_attached_when_the_donation_is_accepted(): void
    {
        $donasi = Donasi::factory()->create(['email' => 'ratna@example.com']);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.donasi.status', $donasi), ['status' => StatusDonasi::Diterima->value]);

        Mail::assertSent(PemberitahuanDonasi::class, function (PemberitahuanDonasi $mail) use ($donasi): bool {
            $mail->assertHasSubject("Terima kasih, donasi Anda sudah kami terima ({$donasi->kunci})");

            return $mail->hasTo('ratna@example.com')
                && count($mail->attachments()) === 1
                && $mail->attachments()[0]->as === "kuitansi-donasi-{$donasi->kunci}.pdf";
        });
    }

    public function test_donor_is_told_when_the_donation_is_rejected(): void
    {
        $donasi = Donasi::factory()->create(['email' => 'ratna@example.com']);

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.donasi.status', $donasi), ['status' => StatusDonasi::Ditolak->value]);

        Mail::assertSent(PemberitahuanDonasi::class, fn (PemberitahuanDonasi $mail): bool => $mail->hasSubject("Donasi Anda belum dapat kami konfirmasi ({$donasi->kunci})")
            && $mail->attachments() === []);
    }

    public function test_no_email_when_status_is_unchanged_or_email_is_blank(): void
    {
        $tanpaEmail = Donasi::factory()->create(['email' => null]);
        $sudahDiterima = Donasi::factory()->diterima()->create();
        $this->actingAs(User::factory()->create());

        $this->patch(route('admin.donasi.status', $tanpaEmail), ['status' => StatusDonasi::Diterima->value]);
        $this->patch(route('admin.donasi.status', $sudahDiterima), ['status' => StatusDonasi::Diterima->value]);

        Mail::assertNothingSent();
    }

    public function test_staff_can_download_receipt_only_for_accepted_donations(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('admin.donasi.kuitansi', Donasi::factory()->diterima()->create()))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('admin.donasi.kuitansi', Donasi::factory()->create()))->assertNotFound();
    }

    public function test_list_offers_whatsapp_message_matching_the_status(): void
    {
        $donasi = Donasi::factory()->diterima()->create(['no_whatsapp' => '081234567890', 'nama_donatur' => 'Ibu Ratna']);

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $donasi->tautanWhatsapp());
        $this->assertStringContainsString('sudah kami terima', $donasi->pesanWhatsapp());

        $this->actingAs(User::factory()->create())
            ->get(route('admin.donasi.index', ['bulan' => 'semua']))
            ->assertSee('https://wa.me/6281234567890', false);
    }
}
