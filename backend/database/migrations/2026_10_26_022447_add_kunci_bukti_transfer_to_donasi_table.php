<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->string('kunci', 10)->nullable()->unique()->after('id');
            $table->string('bukti_transfer')->nullable()->after('keterangan');
            $table->json('bukti_hasil')->nullable()->after('bukti_transfer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->dropUnique(['kunci']);
            $table->dropColumn(['kunci', 'bukti_transfer', 'bukti_hasil']);
        });
    }
};
