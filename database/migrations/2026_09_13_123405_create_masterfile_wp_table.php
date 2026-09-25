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
        Schema::create('masterfile_wp', function (Blueprint $table) {
            $table->string('npwp15', 15)->primary();
            $table->string('admin', 10)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('kpp', 10)->nullable();
            $table->string('cabang', 10)->nullable();
            $table->string('nama', 255)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kelurahan', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('propinsi', 100)->nullable();
            $table->string('jenis', 50)->nullable();
            $table->string('bentuk_hukum', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('klu', 10)->nullable();
            $table->date('tanggal_daftar')->nullable();
            $table->date('tanggal_pkp')->nullable();
            $table->date('tanggal_pkp_cabut')->nullable();
            $table->string('nik', 30)->nullable();
            $table->string('telp', 50)->nullable();
            $table->string('nip_ar', 30)->nullable();
            $table->string('nip_eks', 30)->nullable();
            $table->string('nip_js', 30)->nullable();
            $table->string('npwp16', 16)->nullable();

            // Indexing Kunci untuk Pencarian Cepat
            $table->index('nip_ar', 'idx_mf_nip_ar');
            $table->fulltext('nama', 'idx_fulltext_nama_wp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('masterfile_wp');
    }
};
