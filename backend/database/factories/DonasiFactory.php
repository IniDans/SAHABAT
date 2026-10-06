<?php

namespace Database\Factories;

use App\Enums\MetodePembayaran;
use App\Enums\ProgramDonasi;
use App\Enums\StatusDonasi;
use App\Enums\TampilanDonatur;
use App\Models\Donasi;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Donasi>
 */
class DonasiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_donatur' => fake()->name(),
            'tampil_sebagai' => TampilanDonatur::NamaAsli,
            'no_whatsapp' => '08'.fake()->numerify('##########'),
            'email' => fake()->safeEmail(),
            'program' => fake()->randomElement(ProgramDonasi::cases()),
            'nominal' => fake()->randomElement([10000, 25000, 50000, 100000, 250000, 500000]),
            'metode_pembayaran' => fake()->randomElement(MetodePembayaran::cases()),
            'tanggal_donasi' => now()->toDateString(),
            'status' => StatusDonasi::Menunggu,
        ];
    }

    /**
     * Donasi yang dananya sudah diterima.
     */
    public function diterima(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StatusDonasi::Diterima,
        ]);
    }
}
