<?php

namespace Database\Factories;

use App\Enums\StatusPesan;
use App\Models\Pesan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pesan>
 */
class PesanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->safeEmail(),
            'subjek' => fake()->randomElement(['Donasi sembako bulanan', 'Kunjungan ke panti', 'Konfirmasi donasi', 'Permintaan proposal']),
            'isi' => fake()->sentence(12),
            'status' => StatusPesan::BelumDibaca,
        ];
    }

    /**
     * Pesan dengan status tertentu.
     */
    public function status(StatusPesan $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }
}
