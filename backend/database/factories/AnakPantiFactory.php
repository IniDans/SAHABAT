<?php

namespace Database\Factories;

use App\Enums\JenisKelamin;
use App\Enums\StatusAsuh;
use App\Models\AnakPanti;
use App\Models\WaliAnak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AnakPanti>
 */
class AnakPantiFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'NIK' => fake()->unique()->numerify('3573############'),
            'Nama' => strtoupper(fake()->name()),
            'Jenis_Kelamin' => fake()->randomElement(JenisKelamin::cases()),
            'Tempat_Lahir' => 'Malang',
            'Tanggal_Lahir' => fake()->dateTimeBetween('-17 years', '-4 years')->format('Y-m-d'),
            'Agama' => 'Islam',
            'Status_Anak' => 'Pelajar',
            'ID_Wali' => WaliAnak::factory(),
            'Keterangan' => fake()->randomElement(['Yatim', 'Piatu', 'Dhuafa']),
            'Kesehatan' => 'Sehat',
            'Pendidikan' => 'Sekolah',
            'Status_Asuh' => StatusAsuh::MasihAktif,
        ];
    }

    /**
     * Anak yang sudah tidak diasuh lagi.
     */
    public function alumni(): static
    {
        return $this->state(fn (array $attributes) => [
            'Status_Asuh' => StatusAsuh::Alumni,
        ]);
    }
}
