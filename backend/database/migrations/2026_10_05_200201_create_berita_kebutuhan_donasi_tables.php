<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel untuk panel admin: berita, kebutuhan panti, dan donasi.
 * Tidak ada di demo_pantiyasibu.sql; dikelola sepenuhnya oleh aplikasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berita', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->string('slug')->unique();
            $table->string('kategori', 20);
            $table->string('gambar')->nullable();
            $table->text('ringkasan')->nullable();
            $table->longText('isi');
            $table->string('status', 10)->default('Draft');
            $table->date('tanggal_terbit');
            $table->timestamps();

            $table->index(['status', 'tanggal_terbit']);
        });

        Schema::create('kebutuhan_panti', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->unsignedInteger('jumlah')->nullable();
            $table->string('satuan', 30)->nullable();
            $table->string('prioritas', 10)->default('Sedang');
            $table->unsignedTinyInteger('skor_prioritas')->default(50);
            $table->boolean('terpenuhi')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::create('donasi', function (Blueprint $table) {
            $table->id();
            $table->string('nama_donatur');
            $table->string('no_whatsapp', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('program', 30);
            $table->unsignedBigInteger('nominal');
            $table->string('metode_pembayaran', 20);
            $table->date('tanggal_donasi');
            $table->string('status', 10)->default('Menunggu');
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['status', 'tanggal_donasi']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donasi');
        Schema::dropIfExists('kebutuhan_panti');
        Schema::dropIfExists('berita');
    }
};
