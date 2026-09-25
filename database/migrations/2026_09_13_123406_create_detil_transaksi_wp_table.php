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
        Schema::create('detil_transaksi_wp', function (Blueprint $table) {
            $table->id();
            $table->string('kd_kanwil', 10)->nullable();
            $table->string('kpp_adm', 10)->nullable();
            $table->string('npwp', 20)->nullable();
            $table->string('kpp', 10)->nullable();
            $table->string('cabang', 10)->nullable();
            $table->string('npwp15', 15)->nullable();
            $table->string('nama_wp', 255)->nullable();
            $table->string('no_pbk', 50)->nullable();
            $table->string('ntpn', 50)->nullable();
            $table->date('tgl_setor')->nullable();
            $table->integer('thn_setor')->nullable();
            $table->integer('bln_setor')->nullable();
            $table->integer('thn_pajak')->nullable();
            $table->string('masa_pajak', 10)->nullable();
            $table->decimal('jml_setor', 18, 2)->default(0);
            $table->string('kd_map', 10)->nullable();
            $table->string('kd_bayar', 10)->nullable();
            $table->string('fungsi', 100)->nullable();
            $table->string('jenis', 100)->nullable();
            $table->string('flag_skp', 10)->nullable();
            $table->string('id_sbr_data', 50)->nullable();
            $table->string('tipe', 50)->nullable();

            // Indexing untuk Filter Cepat
            $table->index('npwp15', 'idx_tx_npwp15');
            $table->index(['thn_setor', 'bln_setor'], 'idx_tx_tahun_bulan');
            $table->index(['kd_map', 'kd_bayar'], 'idx_tx_map_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detil_transaksi_wp');
    }
};
