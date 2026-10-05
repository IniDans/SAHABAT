<?php

namespace Tests\Feature\Api;

use App\Models\AnakPanti;
use App\Models\User;
use App\Models\WaliAnak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WaliAnakTest extends TestCase
{
    use RefreshDatabase;

    public function test_guardian_shows_their_children(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $id = $this->postJson('/api/wali-anak', [
            'Nama_Wali' => 'Desi Kristanti',
            'Alamat_Wali' => 'Lowokwaru Jl. Kembang Kertas',
        ])->assertCreated()->json('data.ID_Wali');

        AnakPanti::factory(2)->create(['ID_Wali' => $id]);

        $this->getJson("/api/wali-anak/{$id}")
            ->assertOk()->assertJsonCount(2, 'data.anak');

        $this->getJson('/api/wali-anak?search=desi')
            ->assertOk()->assertJsonPath('data.0.jumlah_anak', 2);
    }

    public function test_deleting_a_guardian_keeps_the_children(): void
    {
        Sanctum::actingAs(User::factory()->admin()->create());
        $wali = WaliAnak::factory()->create();
        $anak = AnakPanti::factory()->for($wali, 'wali')->create();

        $this->deleteJson("/api/wali-anak/{$wali->ID_Wali}")->assertNoContent();

        $this->assertNull($anak->fresh()->ID_Wali);
    }
}
