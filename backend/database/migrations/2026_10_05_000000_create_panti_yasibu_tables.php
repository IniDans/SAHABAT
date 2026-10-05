<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Struktur mengikuti database/sql/demo_pantiyasibu.sql.
 *
 * Tabel yang sudah ada dilewati, jadi migration ini aman dijalankan
 * setelah file SQL tersebut di-import langsung (mis. lewat phpMyAdmin).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('wali_anak')) {
            Schema::create('wali_anak', function (Blueprint $table) {
                $table->integer('ID_Wali', autoIncrement: true);
                $table->string('Nama_Wali', 100);
                $table->string('Alamat_Wali', 100);
            });
        }

        if (! Schema::hasTable('anak_panti')) {
            Schema::create('anak_panti', function (Blueprint $table) {
                $table->string('NIK', 20)->primary();
                $table->string('Nama', 100);
                $table->enum('Jenis_Kelamin', ['Laki-Laki', 'Perempuan']);
                $table->string('Tempat_Lahir', 50);
                $table->date('Tanggal_Lahir')->nullable();
                $table->string('Agama', 10);
                $table->string('Status_Anak', 20);
                $table->integer('ID_Wali')->nullable();
                $table->string('Keterangan', 50)->default('Dhuafa');
                $table->string('Ukuran_Pakaian', 20)->nullable();
                $table->string('Ukuran_Sepatu', 20)->nullable();
                $table->string('Kesehatan', 100)->nullable();
                $table->string('Pendidikan', 100);
                $table->enum('Status_Asuh', ['Masih Aktif', 'Alumni'])->default('Masih Aktif');

                $table->foreign('ID_Wali', 'fk_anak_wali')
                    ->references('ID_Wali')->on('wali_anak')
                    ->nullOnDelete()->cascadeOnUpdate();
            });
        }

        if (! Schema::hasTable('pengasuh')) {
            Schema::create('pengasuh', function (Blueprint $table) {
                $table->string('NIK', 20)->primary();
                $table->string('Nama', 100);
                $table->string('Jabatan', 50);
                $table->enum('Jenis_Kelamin', ['Laki-Laki', 'Perempuan']);
                $table->string('Tempat_Lahir', 100);
                $table->date('Tanggal_Lahir')->nullable();
                $table->string('Agama', 10);
                $table->string('Email', 50);
                $table->string('Pendidikan', 100);
                $table->string('Kesehatan', 100)->nullable();
                $table->string('Nomor_Telepon', 20);
                $table->string('Alamat', 100);
            });
        }

        if (! Schema::hasTable('kegiatan_panti')) {
            Schema::create('kegiatan_panti', function (Blueprint $table) {
                $table->integer('id_kegiatan', autoIncrement: true);
                $table->string('nama_kegiatan', 150);
                $table->enum('jenis_kegiatan', ['Ibadah', 'Pendidikan', 'Kreativitas', 'Sosial', 'Olahraga', 'Lainnya']);
                $table->text('deskripsi')->nullable();
                $table->date('tanggal_kegiatan');
                $table->string('lokasi', 100)->nullable();
                $table->string('nama_foto')->nullable()->default('default_kegiatan.jpg');
                $table->timestamp('created_at')->useCurrent();
            });
        }

        DB::statement('DROP VIEW IF EXISTS v_anak_aktif_lengkap');
        DB::statement(<<<'SQL'
            CREATE VIEW v_anak_aktif_lengkap AS
            SELECT ap.NIK AS NIK, ap.Nama AS Nama_Anak, ap.Jenis_Kelamin AS Jenis_Kelamin,
                   ap.Pendidikan AS Pendidikan, w.Nama_Wali AS Nama_Wali, w.Alamat_Wali AS Alamat_Wali
            FROM anak_panti ap
            LEFT JOIN wali_anak w ON ap.ID_Wali = w.ID_Wali
            WHERE ap.Status_Asuh = 'Masih Aktif'
            SQL);

        if (! $this->isMysql()) {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS get_alumni');
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE get_alumni()
            SELECT ap.NIK, ap.Nama, ap.Tanggal_Lahir, ap.Pendidikan, ap.Keterangan, w.Nama_Wali
            FROM anak_panti ap
            LEFT JOIN wali_anak w ON ap.ID_Wali = w.ID_Wali
            WHERE ap.Status_Asuh = 'Alumni'
            ORDER BY ap.Nama ASC
            SQL);

        DB::unprepared('DROP PROCEDURE IF EXISTS get_anak_aktif');
        DB::unprepared(<<<'SQL'
            CREATE PROCEDURE get_anak_aktif()
            SELECT ap.NIK, ap.Nama, ap.Jenis_Kelamin, ap.Tanggal_Lahir, ap.Keterangan,
                   ap.Pendidikan, ap.Status_Asuh, w.Nama_Wali, w.Alamat_Wali
            FROM anak_panti ap
            LEFT JOIN wali_anak w ON ap.ID_Wali = w.ID_Wali
            WHERE ap.Status_Asuh = 'Masih Aktif'
            ORDER BY ap.Nama ASC
            SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_cegah_hapus_anak_aktif');
        DB::unprepared(<<<'SQL'
            CREATE TRIGGER trg_cegah_hapus_anak_aktif BEFORE DELETE ON anak_panti FOR EACH ROW
            BEGIN
                IF OLD.Status_Asuh = 'Masih Aktif' THEN
                    SIGNAL SQLSTATE '45000'
                    SET MESSAGE_TEXT = 'Anak yang masih aktif tidak dapat dihapus dari sistem.';
                END IF;
            END
            SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->isMysql()) {
            DB::unprepared('DROP PROCEDURE IF EXISTS get_alumni');
            DB::unprepared('DROP PROCEDURE IF EXISTS get_anak_aktif');
        }

        DB::statement('DROP VIEW IF EXISTS v_anak_aktif_lengkap');

        Schema::dropIfExists('kegiatan_panti');
        Schema::dropIfExists('pengasuh');
        Schema::dropIfExists('anak_panti');
        Schema::dropIfExists('wali_anak');
    }

    /**
     * Stored procedures and triggers use MySQL/MariaDB syntax.
     */
    private function isMysql(): bool
    {
        return in_array(DB::getDriverName(), ['mysql', 'mariadb'], true);
    }
};
