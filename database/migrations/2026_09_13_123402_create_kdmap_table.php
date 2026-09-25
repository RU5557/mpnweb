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
        Schema::create('kdmap', function (Blueprint $table) {
            $table->id();
            $table->string('kd_map', 10);
            $table->string('kd_bayar', 10);
            $table->string('jenis_pajak', 100)->nullable();
            $table->string('jenis_bayar', 255)->nullable();
            $table->string('sektor_pajak', 100)->nullable();

            $table->unique(['kd_map', 'kd_bayar'], 'idx_kdmap_bayar');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kdmap');
    }
};
