<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan siapa mengubah apa di panel admin. Isi data tidak disalin ke sini,
 * hanya nama kolom yang berubah, supaya data pribadi anak tidak tersebar ke tabel lain.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('log_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nama_pengguna')->nullable();
            $table->string('aksi', 20);
            $table->string('subjek_tipe', 50)->nullable();
            $table->string('subjek_id', 50)->nullable();
            $table->string('subjek_label')->nullable();
            $table->json('kolom')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('created_at');
            $table->index(['subjek_tipe', 'subjek_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_aktivitas');
    }
};
