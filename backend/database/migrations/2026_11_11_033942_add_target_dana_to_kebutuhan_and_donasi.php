<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kebutuhan panti bisa diberi target dana, dan donasi bisa ditujukan ke satu kebutuhan.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kebutuhan_panti', function (Blueprint $table) {
            $table->unsignedBigInteger('target_dana')->nullable()->after('satuan');
        });

        Schema::table('donasi', function (Blueprint $table) {
            $table->foreignId('kebutuhan_panti_id')->nullable()->after('program')->constrained('kebutuhan_panti')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kebutuhan_panti_id');
        });

        Schema::table('kebutuhan_panti', function (Blueprint $table) {
            $table->dropColumn('target_dana');
        });
    }
};
