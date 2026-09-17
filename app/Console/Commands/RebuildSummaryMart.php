<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

class RebuildSummaryMart extends Command
{
    protected $signature = 'summary:rebuild';
    protected $description = 'Rekapitulasi total penerimaan ke summary_mart_penerimaan';

    public function handle()
    {
        $this->comment('-> Memulai rekapitulasi summary mart penerimaan...');
        $startTime = microtime(true);

        try {
            // Dihapus prefix schema 'mpnweb.' agar dinamis sesuai .env
            DB::statement('TRUNCATE TABLE summary_mart_penerimaan;');

            DB::statement("
                INSERT INTO summary_mart_penerimaan (
                    thn_setor, bln_setor, jenis, fungsi, total_setor, total_transaksi, created_at, updated_at
                )
                SELECT 
                    thn_setor,
                    bln_setor,
                    COALESCE(jenis, '') AS jenis,
                    COALESCE(fungsi, '') AS fungsi,
                    SUM(jml_setor) AS total_setor,
                    COUNT(*) AS total_transaksi,
                    NOW(),
                    NOW()
                FROM detil_transaksi_wp
                GROUP BY thn_setor, bln_setor, jenis, fungsi
            ");

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("   [OK] Summary Mart berhasil diperbarui dalam {$executionTime} detik!");
            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error("   [ERROR] Gagal rebuild summary mart: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}