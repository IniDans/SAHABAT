<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Isi halaman Tentang Kami dan footer website yang diatur admin, satu baris per bagian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profil_panti', function (Blueprint $table) {
            $table->string('kunci', 50)->primary();
            $table->json('nilai');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profil_panti');
    }
};
