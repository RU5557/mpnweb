<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('spt_coretax', function (Blueprint $table) {
            // 1. Tambah Auto-Increment Primary Key
            $table->id()->first();

            // 2. Index untuk klausa JOIN ke masterfile_wp
            $table->index('npwp', 'idx_spt_npwp');

            // 3. Index gabungan untuk pencarian & filtering
            $table->index(['thn_pajak', 'jenis_spt', 'status_spt'], 'idx_spt_search_filter');
            $table->index('tgl_terima', 'idx_spt_tgl_terima');
            $table->index(['masa1', 'masa2'], 'idx_spt_masa');
            $table->index('nama', 'idx_spt_nama');
        });
    }

    public function down(): void
    {
        Schema::table('spt_coretax', function (Blueprint $table) {
            $table->dropId('id');
            $table->dropIndex('idx_spt_npwp');
            $table->dropIndex('idx_spt_search_filter');
            $table->dropIndex('idx_spt_tgl_terima');
            $table->dropIndex('idx_spt_masa');
            $table->dropIndex('idx_spt_nama');
        });
    }
};
