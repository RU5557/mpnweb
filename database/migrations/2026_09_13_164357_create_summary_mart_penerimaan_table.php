<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('summary_mart_penerimaan', function (Blueprint $table) {
            $table->id();
            $table->integer('thn_setor')->index();
            $table->integer('bln_setor')->index();
            $table->string('jenis', 50)->nullable()->index();
            $table->string('fungsi', 50)->nullable()->index();
            $table->decimal('total_setor', 18, 2)->default(0);
            $table->integer('total_transaksi')->default(0);
            $table->timestamps();

            // Index gabungan agar query filter dashboard sangat cepat
            $table->index(['thn_setor', 'bln_setor', 'jenis', 'fungsi'], 'idx_summary_lookup');
        });
    }

    public function down(): void {
        Schema::dropIfExists('summary_mart_penerimaan');
    }
};