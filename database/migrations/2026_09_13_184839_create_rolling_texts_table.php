<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rolling_texts', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->decimal('nko', 5, 2)->default(0); // Contoh: 95.50
            $table->integer('ranking_nasional')->default(0);
            $table->integer('ranking_kanwil')->default(0);
            $table->text('pesan_tambahan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rolling_texts');
    }
};