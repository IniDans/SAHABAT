<?php

namespace Tests\Feature;

use App\Models\Berita;
use App\Models\Program;
use Database\Seeders\KontenSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KontenSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_published_posts_even_without_model_events(): void
    {
        Storage::fake('public');

        Model::withoutEvents(fn () => $this->seed(KontenSeeder::class));

        $program = Program::query()->terbit()->where('tampil_di_beranda', true)->firstOrFail();
        $this->assertSame(6, Berita::query()->terbit()->count());
        $this->assertNotEmpty($program->slug);
        Storage::disk('public')->assertExists($program->gambar);

        $this->get(route('program.show', $program->slug))->assertOk();
    }

    public function test_it_leaves_existing_posts_alone(): void
    {
        Berita::factory()->create();

        $this->seed(KontenSeeder::class);

        $this->assertSame(1, Berita::count());
        $this->assertSame(0, Program::count());
    }
}
