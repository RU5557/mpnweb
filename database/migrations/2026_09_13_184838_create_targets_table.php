<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun');
            $table->decimal('target_kantor', 15, 2)->default(0);
            $table->decimal('target_ppm', 15, 2)->default(0);
            $table->decimal('target_pkm', 15, 2)->default(0);
            $table->decimal('target_pbp', 15, 2)->default(0);
            $table->decimal('target_pkm_pengawasan', 15, 2)->default(0);
            $table->decimal('target_pkm_pemeriksaan', 15, 2)->default(0);
            $table->decimal('target_pkm_penagihan', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};