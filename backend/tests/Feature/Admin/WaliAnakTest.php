<?php

namespace Tests\Feature\Admin;

use App\Models\AnakPanti;
use App\Models\User;
use App\Models\WaliAnak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaliAnakTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_get_not_found(): void
    {
        $this->get(route('admin.wali-anak.index'))->assertNotFound();
    }

    public function test_index_shows_how_many_children_each_guardian_has(): void
    {
        $wali = WaliAnak::factory()->create(['Nama_Wali' => 'Pak Harun']);
        AnakPanti::factory()->count(2)->create(['ID_Wali' => $wali->ID_Wali]);

        $this->actingAs(User::factory()->create())
            ->get(route('admin.wali-anak.index'))
            ->assertOk()
            ->assertSeeInOrder(['Pak Harun', '2 anak']);
    }

    public function test_wali_can_be_created_and_updated(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.wali-anak.create'))
            ->assertOk();

        $this->post(route('admin.wali-anak.store'), ['Nama_Wali' => 'Pak Harun', 'Alamat_Wali' => 'Lowokwaru'])
            ->assertRedirect(route('admin.wali-anak.index'));

        $wali = WaliAnak::sole();
        $this->assertSame('Pak Harun', $wali->Nama_Wali);

        $this->get(route('admin.wali-anak.edit', $wali))->assertOk();

        $this->put(route('admin.wali-anak.update', $wali), ['Nama_Wali' => 'Pak Harun', 'Alamat_Wali' => 'Blimbing'])
            ->assertRedirect(route('admin.wali-anak.index'));

        $this->assertSame('Blimbing', $wali->fresh()->Alamat_Wali);
    }

    public function test_empty_input_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('admin.wali-anak.store'), [])
            ->assertSessionHasErrors([
                'Nama_Wali' => 'The nama wali field is required.',
                'Alamat_Wali' => 'The alamat wali field is required.',
            ]);
    }

    public function test_deleting_a_wali_keeps_their_children(): void
    {
        $wali = WaliAnak::factory()->create();
        $anak = AnakPanti::factory()->create(['ID_Wali' => $wali->ID_Wali]);

        $this->actingAs(User::factory()->create())
            ->delete(route('admin.wali-anak.destroy', $wali))
            ->assertForbidden();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.wali-anak.destroy', $wali))
            ->assertRedirect(route('admin.wali-anak.index'));

        $this->assertModelMissing($wali);
        $this->assertModelExists($anak);
    }
}
