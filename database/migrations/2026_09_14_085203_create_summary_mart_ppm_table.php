<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summary_mart_ppm', function (Blueprint $table) {
            $table->id();
            
            // Periode
            $table->integer('thn_setor');
            $table->integer('bln_setor');
            $table->string('jenis', 50)->default('PPM');
            
            // Atribut WP
            $table->string('npwp', 15)->nullable();
            $table->string('nama_wp', 255)->nullable();
            
            // Atribut Sektor (KLU)
            $table->string('kd_kategori', 10)->nullable();
            $table->string('nm_kategori', 255)->nullable();
            
            // Atribut MAP / Jenis Pajak
            $table->string('kd_map', 10)->nullable();
            $table->string('jenis_pajak', 255)->nullable();
            
            // Nominal
            $table->decimal('jml_setor', 18, 2)->default(0.00);
            $table->integer('total_transaksi')->default(0);
            
            $table->timestamps();

            // Index wajib biar query Top 10 instan!
            $table->index(['thn_setor', 'bln_setor', 'jenis']);
            $table->index('npwp');
            $table->index('kd_map');
            $table->index('kd_kategori');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summary_mart_ppm');
    }
};