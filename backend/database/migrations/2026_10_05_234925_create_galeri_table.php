<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto kegiatan untuk halaman Galeri, urut sesuai susunan admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galeri', function (Blueprint $table) {
            $table->id();
            $table->string('gambar');
            $table->string('keterangan', 150)->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();

            $table->index('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galeri');
    }
};
