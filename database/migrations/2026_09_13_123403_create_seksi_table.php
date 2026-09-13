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
    Schema::create('seksi', function (Blueprint $table) {
        $table->unsignedBigInteger('id')->primary();
        $table->string('kantor', 10)->nullable();
        $table->string('tipe', 50)->nullable();
        $table->string('nama', 150)->nullable();
        $table->string('kode', 20)->nullable();
        $table->string('telp', 50)->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seksi');
    }
};
