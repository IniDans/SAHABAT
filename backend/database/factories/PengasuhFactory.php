<?php

namespace Database\Factories;

use App\Enums\JenisKelamin;
use App\Models\Pengasuh;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pengasuh>
 */
class PengasuhFactory extends Factory
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
            'Nama' => fake()->name(),
            'Jabatan' => fake()->randomElement(['Pengasuh', 'Admin', 'Bendahara']),
            'Jenis_Kelamin' => fake()->randomElement(JenisKelamin::cases()),
            'Tempat_Lahir' => 'Malang',
            'Tanggal_Lahir' => fake()->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
            'Agama' => 'Islam',
            'Email' => fake()->unique()->safeEmail(),
            'Pendidikan' => 'S1',
            'Kesehatan' => 'Sehat',
            'Nomor_Telepon' => fake()->numerify('08##########'),
            'Alamat' => 'Jl. '.fake()->streetName().', Malang',
        ];
    }
}
