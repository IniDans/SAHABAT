<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hasil timbang berat badan anak, satu catatan per anak per bulan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berat_badan', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20);
            $table->date('bulan')->comment('Tanggal 1 bulan penimbangan');
            $table->decimal('berat_kg', 5, 1);
            $table->timestamps();

            $table->unique(['nik', 'bulan']);
            $table->index('bulan');
            $table->foreign('nik', 'fk_berat_badan_anak')
                ->references('NIK')->on('anak_panti')
                ->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berat_badan');
    }
};
