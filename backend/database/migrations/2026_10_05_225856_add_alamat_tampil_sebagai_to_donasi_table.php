<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Program lama dipetakan ke program terdekat di desain baru.
     *
     * @var array<string, string>
     */
    private array $programBaru = [
        'Pendidikan' => 'Beasiswa',
        'Ramadhan' => 'Sedekah',
        'Infaq & Sedekah' => 'Infak',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->string('tampil_sebagai', 20)->default('Nama asli')->after('nama_donatur');
            $table->text('alamat')->nullable()->after('email');
        });

        foreach ($this->programBaru as $lama => $baru) {
            DB::table('donasi')->where('program', $lama)->update(['program' => $baru]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('donasi')->where('program', 'Beasiswa')->update(['program' => 'Pendidikan']);
        DB::table('donasi')->whereIn('program', ['Infak', 'Sedekah', 'Wakaf'])->update(['program' => 'Infaq & Sedekah']);

        Schema::table('donasi', function (Blueprint $table) {
            $table->dropColumn(['tampil_sebagai', 'alamat']);
        });
    }
};
