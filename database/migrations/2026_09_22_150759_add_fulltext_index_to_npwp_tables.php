<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tambahkan FULLTEXT Index untuk NPWP15 & NPWP16 di masterfile_wp
        DB::statement('ALTER TABLE masterfile_wp ADD FULLTEXT KEY ft_npwp (npwp15, npwp16)');

        // Tambahkan FULLTEXT Index untuk NPWP15 di detil_transaksi_wp
        DB::statement('ALTER TABLE detil_transaksi_wp ADD FULLTEXT KEY ft_dtl_npwp (npwp15)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus index jika migration di-rollback
        DB::statement('ALTER TABLE masterfile_wp DROP INDEX ft_npwp');
        DB::statement('ALTER TABLE detil_transaksi_wp DROP INDEX ft_dtl_npwp');
    }
};