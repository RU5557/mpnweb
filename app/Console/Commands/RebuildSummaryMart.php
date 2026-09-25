<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildSummaryMart extends Command
{
    protected $signature = 'summary:rebuild';

    protected $description = 'Rekapitulasi total penerimaan ke summary_mart_penerimaan menggunakan teknik Temp-Table Swap';

    public function handle()
    {
        $this->comment('-> Memulai rekapitulasi summary mart penerimaan...');
        $startTime = microtime(true);

        try {
            // 1. Buat tabel sementara yang identik secara struktur
            DB::statement('CREATE TABLE IF NOT EXISTS summary_mart_penerimaan_temp LIKE summary_mart_penerimaan;');
            DB::statement('TRUNCATE TABLE summary_mart_penerimaan_temp;');

            // 2. Insert data hasil kalkulasi agregasi ke tabel sementara
            DB::statement("
                INSERT INTO summary_mart_penerimaan_temp (
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

            // 3. Swap tabel secara atomic (Instan < 0.01 detik tanpa downtime)
            DB::statement('CREATE TABLE IF NOT EXISTS summary_mart_penerimaan_old LIKE summary_mart_penerimaan;');

            DB::statement('RENAME TABLE 
                summary_mart_penerimaan TO summary_mart_penerimaan_old,
                summary_mart_penerimaan_temp TO summary_mart_penerimaan;
            ');

            // 4. Hapus tabel lama
            DB::statement('DROP TABLE IF EXISTS summary_mart_penerimaan_old;');

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("   [OK] Summary Mart berhasil diperbarui dalam {$executionTime} detik!");

            return Command::SUCCESS;

        } catch (Exception $e) {
            // Fallback jika terjadi error pada Temp Swap: Jalankan metode langsung
            $this->warn('   [!] Menjalankan fallback direct rebuild...');
            try {
                DB::statement('TRUNCATE TABLE summary_mart_penerimaan;');
                DB::statement("
                    INSERT INTO summary_mart_penerimaan (
                        thn_setor, bln_setor, jenis, fungsi, total_setor, total_transaksi, created_at, updated_at
                    )
                    SELECT 
                        thn_setor, bln_setor, COALESCE(jenis, ''), COALESCE(fungsi, ''), 
                        SUM(jml_setor), COUNT(*), NOW(), NOW()
                    FROM detil_transaksi_wp
                    GROUP BY thn_setor, bln_setor, jenis, fungsi
                ");

                return Command::SUCCESS;
            } catch (Exception $fallbackEx) {
                $this->error('   [ERROR] Gagal rebuild summary mart: '.$fallbackEx->getMessage());

                return Command::FAILURE;
            }
        }
    }
}
