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
    Schema::create('summary_penerimaan_kpp', function (Blueprint $table) {
        $table->integer('thn_setor');
        $table->integer('bln_setor');
        $table->string('kpp_adm', 10)->default('000');
        $table->string('kd_map', 10)->default('');
        $table->string('kd_bayar', 10)->default('');
        $table->decimal('total_setor', 20, 2)->default(0);
        $table->bigInteger('total_transaksi')->default(0);

        // Composite Primary Key (Anti Duit Dobel)
        $table->primary(['thn_setor', 'bln_setor', 'kpp_adm', 'kd_map', 'kd_bayar'], 'pk_summary_penerimaan');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('summary_penerimaan_kpp');
    }
};
