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
    Schema::create('pegawai', function (Blueprint $table) {
        $table->id();
        $table->string('nip', 30);
        $table->integer('tahun');
        $table->string('kantor', 10)->nullable();
        $table->string('nip2', 30)->nullable();
        $table->string('nama', 150)->nullable();
        $table->string('pangkat', 100)->nullable();
        $table->string('seksi', 100)->nullable();
        $table->string('jabatan', 150)->nullable();
        $table->string('plh', 10)->nullable();

        $table->index(['nip', 'tahun'], 'idx_pegawai_nip_tahun');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pegawai');
    }
};
