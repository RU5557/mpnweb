<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pkm_penagihan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_seksi');
            $table->string('nama_juru_sita'); // Atau nama penagih/Jurusita
            $table->decimal('pkm_penagihan', 15, 2)->default(0);
            $table->decimal('pkm_lainnya', 15, 2)->default(0);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pkm_penagihan');
    }
};