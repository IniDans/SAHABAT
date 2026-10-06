<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Akun awal untuk pengembangan. Segera ganti password-nya
     * sebelum aplikasi dipakai sungguhan.
     */
    public function run(): void
    {
        User::firstOrCreate(['email' => 'admin@sahabat.test'], [
            'name' => 'Admin SAHABAT',
            'password' => 'password',
            'role' => Role::Admin,
        ]);

        User::firstOrCreate(['email' => 'pengurus@sahabat.test'], [
            'name' => 'Pengurus SAHABAT',
            'password' => 'password',
            'role' => Role::Pengurus,
        ]);

        $this->call([DemoSeeder::class, KontenSeeder::class, BeratBadanSeeder::class]);
    }
}
