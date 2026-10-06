<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Program panti (beasiswa, pendidikan, dsb.) yang tampil di halaman Program dan Beranda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori', 20);
            $table->string('gambar')->nullable();
            $table->text('ringkasan')->nullable();
            $table->longText('isi');
            $table->string('status', 10)->default('Draft');
            $table->boolean('tampil_di_beranda')->default(false);
            $table->date('tanggal_terbit')->nullable();
            $table->timestamps();

            $table->index(['status', 'tanggal_terbit']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program');
    }
};
