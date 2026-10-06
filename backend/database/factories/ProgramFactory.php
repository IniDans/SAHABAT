<?php

namespace Database\Factories;

use App\Enums\KategoriProgram;
use App\Enums\StatusBerita;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'judul' => fake()->sentence(5),
            'kategori' => fake()->randomElement(KategoriProgram::cases())->value,
            'ringkasan' => fake()->sentence(15),
            'isi' => '<p>'.fake()->paragraph().'</p>',
            'status' => StatusBerita::Terbit,
            'tampil_di_beranda' => false,
            'tanggal_terbit' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
        ];
    }

    /**
     * Program yang belum diterbitkan.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusBerita::Draft,
            'tanggal_terbit' => null,
        ]);
    }

    /**
     * Program yang tampil di bagian Program pada Beranda.
     */
    public function diBeranda(): static
    {
        return $this->state(fn (array $attributes) => [
            'tampil_di_beranda' => true,
        ]);
    }
}
