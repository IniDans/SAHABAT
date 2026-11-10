<?php

namespace Database\Factories;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogAktivitas>
 */
class LogAktivitasFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'nama_pengguna' => fn (array $attributes) => User::find($attributes['user_id'])?->name,
            'aksi' => LogAktivitas::AKSI_UBAH,
            'subjek_tipe' => 'KebutuhanPanti',
            'subjek_id' => (string) fake()->numberBetween(1, 100),
            'subjek_label' => fake()->randomElement(['Beras', 'Susu anak', 'Seragam sekolah']),
            'kolom' => ['jumlah'],
            'ip' => fake()->ipv4(),
            'created_at' => now(),
        ];
    }
}
