<?php

namespace Tests\Feature;

use App\Models\AnakPanti;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnakAsuhPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_children_are_paginated_by_25(): void
    {
        AnakPanti::factory()->count(26)->create();

        $this->get(route('tentang.anak-asuh'))
            ->assertOk()
            ->assertSee('Menampilkan 1 sampai 25 dari 26 data');

        $this->get(route('tentang.anak-asuh', ['page' => 2]))
            ->assertSee('Menampilkan 26 sampai 26 dari 26 data');
    }

    public function test_children_can_be_searched_and_sorted(): void
    {
        AnakPanti::factory()->create(['Nama' => 'ADITYA RAMADHAN']);
        AnakPanti::factory()->create(['Nama' => 'ZAHIRA AURA']);
        AnakPanti::factory()->create(['Nama' => 'BUNGA CITRA']);

        $this->get(route('tentang.anak-asuh', ['q' => 'zahira']))
            ->assertSee('ZAHIRA AURA')
            ->assertDontSee('ADITYA RAMADHAN');

        $this->get(route('tentang.anak-asuh', ['urut' => 'desc']))
            ->assertSeeInOrder(['ZAHIRA AURA', 'BUNGA CITRA', 'ADITYA RAMADHAN']);
    }
}
