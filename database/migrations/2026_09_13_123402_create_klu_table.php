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
    Schema::create('klu', function (Blueprint $table) {
        $table->string('kd_klu', 10)->primary();
        $table->string('nm_klu', 255)->nullable();
        $table->string('kd_kategori', 10)->nullable();
        $table->string('nm_kategori', 255)->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klu');
    }
};
