<?php

namespace Tests\Feature\Admin;

use App\Enums\JenisKelamin;
use App\Models\Pengasuh;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengasuhTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.pengasuh.index'))->assertNotFound();
    }

    public function test_index_searches_by_name(): void
    {
        Pengasuh::factory()->create(['Nama' => 'Ibu Aminah']);
        Pengasuh::factory()->create(['Nama' => 'Pak Joko']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.pengasuh.index', ['search' => 'Aminah']))
            ->assertOk()
            ->assertSee('Ibu Aminah')
            ->assertDontSee('Pak Joko');
    }

    public function test_pengasuh_can_be_created(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.pengasuh.create'))
            ->assertOk();

        $this->post(route('admin.pengasuh.store'), $this->payload())
            ->assertRedirect(route('admin.pengasuh.index'))
            ->assertSessionHas('status');

        $pengasuh = Pengasuh::sole();
        $this->assertSame('3573010101010001', $pengasuh->NIK);
        $this->assertSame('Ibu Aminah', $pengasuh->Nama);
        $this->assertSame(JenisKelamin::Perempuan, $pengasuh->Jenis_Kelamin);
    }

    public function test_invalid_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.pengasuh.store'), $this->payload(['NIK' => '35A73', 'Email' => 'bukan-email', 'Nomor_Telepon' => 'telepon']))
            ->assertSessionHasErrors([
                'NIK' => 'NIK hanya boleh berisi angka, maksimal 20 digit.',
                'Email',
                'Nomor_Telepon' => 'Nomor telepon tidak valid. Contoh: 081234567890.',
            ]);

        $this->assertSame(0, Pengasuh::count());
    }

    public function test_nik_must_be_unique(): void
    {
        Pengasuh::factory()->create(['NIK' => '3573010101010001']);

        $this->actingAs(User::factory()->create())
            ->post(route('admin.pengasuh.store'), $this->payload())
            ->assertSessionHasErrors('NIK');
    }

    public function test_pengasuh_can_be_updated(): void
    {
        $pengasuh = Pengasuh::factory()->create(['NIK' => '3573010101010001']);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.pengasuh.edit', $pengasuh))
            ->assertOk();

        $this->put(route('admin.pengasuh.update', $pengasuh), $this->payload(['Jabatan' => 'Kepala Pengasuh']))
            ->assertRedirect(route('admin.pengasuh.index'));

        $this->assertSame('Kepala Pengasuh', $pengasuh->fresh()->Jabatan);
    }

    public function test_only_admin_can_delete(): void
    {
        $pengasuh = Pengasuh::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.pengasuh.destroy', $pengasuh))
            ->assertForbidden();
        $this->assertModelExists($pengasuh);

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.pengasuh.destroy', $pengasuh))
            ->assertRedirect(route('admin.pengasuh.index'));
        $this->assertModelMissing($pengasuh);
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    private function payload(array $override = []): array
    {
        return [
            'NIK' => '3573010101010001',
            'Nama' => 'Ibu Aminah',
            'Jabatan' => 'Pengasuh',
            'Jenis_Kelamin' => JenisKelamin::Perempuan->value,
            'Tempat_Lahir' => 'Malang',
            'Tanggal_Lahir' => '1980-05-12',
            'Agama' => 'Islam',
            'Email' => 'aminah@example.com',
            'Pendidikan' => 'SMA',
            'Kesehatan' => 'Sehat',
            'Nomor_Telepon' => '081234567890',
            'Alamat' => 'Jl. Babatan, Malang',
            ...$override,
        ];
    }
}
