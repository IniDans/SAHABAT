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
        Schema::table('kebutuhan_panti', function (Blueprint $table) {
            $table->string('kategori', 20)->default('Perlengkapan')->after('nama');
            $table->index('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kebutuhan_panti', function (Blueprint $table) {
            $table->dropIndex(['kategori']);
            $table->dropColumn('kategori');
        });
    }
};
