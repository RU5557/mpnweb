<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildSummaryMart extends Command
{
    protected $signature = 'summary:rebuild 
                            {--thnsetor= : Filter tahun setor} 
                            {--blnsetor= : Filter bulan setor}';

    protected $description = 'Rekapitulasi total penerimaan ke summary_mart_penerimaan menggunakan teknik Temp-Table Swap atau Partial Sync';

    public function handle()
    {
        $thnSetor = $this->option('thnsetor');
        $blnSetor = $this->option('blnsetor');

        $this->comment('-> Memulai rekapitulasi summary mart penerimaan...');
        $startTime = microtime(true);

        if ($thnSetor || $blnSetor) {
            return $this->rebuildPartial($thnSetor, $blnSetor, $startTime);
        }

        return $this->rebuildFull($startTime);
    }

    private function rebuildFull($startTime)
    {
        try {
            $now = now()->toDateTimeString();

            DB::statement('CREATE TABLE IF NOT EXISTS summary_mart_penerimaan_temp LIKE summary_mart_penerimaan;');
            DB::statement('TRUNCATE TABLE summary_mart_penerimaan_temp;');

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
                    '{$now}',
                    '{$now}'
                FROM detil_transaksi_wp
                GROUP BY thn_setor, bln_setor, jenis, fungsi
            ");

            DB::statement('CREATE TABLE IF NOT EXISTS summary_mart_penerimaan_old LIKE summary_mart_penerimaan;');

            DB::statement('RENAME TABLE 
                summary_mart_penerimaan TO summary_mart_penerimaan_old,
                summary_mart_penerimaan_temp TO summary_mart_penerimaan;
            ');

            DB::statement('DROP TABLE IF EXISTS summary_mart_penerimaan_old;');

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("   [OK] Summary Mart FULL berhasil diperbarui dalam {$executionTime} detik!");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->warn('   [!] Menjalankan fallback direct rebuild...');
            try {
                $now = now()->toDateTimeString();
                DB::statement('TRUNCATE TABLE summary_mart_penerimaan;');
                DB::statement("
                    INSERT INTO summary_mart_penerimaan (
                        thn_setor, bln_setor, jenis, fungsi, total_setor, total_transaksi, created_at, updated_at
                    )
                    SELECT 
                        thn_setor, bln_setor, COALESCE(jenis, ''), COALESCE(fungsi, ''), 
                        SUM(jml_setor), COUNT(*), '{$now}', '{$now}'
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

    private function rebuildPartial($thnSetor, $blnSetor, $startTime)
    {
        try {
            $now = now()->toDateTimeString();
            $whereConditions = [];
            if (! empty($thnSetor)) {
                $whereConditions[] = 'thn_setor = '.(int) $thnSetor;
            }
            if (! empty($blnSetor)) {
                $whereConditions[] = 'bln_setor = '.(int) $blnSetor;
            }

            $whereSql = ' WHERE '.implode(' AND ', $whereConditions);

            DB::statement("DELETE FROM summary_mart_penerimaan{$whereSql};");

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
                    '{$now}',
                    '{$now}'
                FROM detil_transaksi_wp
                {$whereSql}
                GROUP BY thn_setor, bln_setor, jenis, fungsi
            ");

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("   [OK] Summary Mart PARTIAL berhasil diperbarui dalam {$executionTime} detik!");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('   [ERROR] Gagal partial rebuild summary mart: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
