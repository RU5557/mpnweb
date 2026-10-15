<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildSummaryMart extends Command
{
    /**
     * Nama dan tanda tangan dari console command.
     */
    protected $signature = 'summary:rebuild 
                            {--thnsetor= : Filter tahun setor (contoh: 2026)} 
                            {--blnsetor= : Filter bulan setor (contoh: 09 atau 9)}';

    /**
     * Deskripsi console command.
     */
    protected $description = 'Rekapitulasi total penerimaan dari tabel drm ke summary_mart_penerimaan menggunakan teknik Temp-Table Swap atau Partial Sync';

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

    /**
     * Full Rebuild menggunakan teknik Atomic Table Swapping (Zero Downtime)
     */
    private function rebuildFull($startTime)
    {
        try {
            $now = now()->toDateTimeString();

            // 1. Buat tabel temp dengan struktur persis summary_mart_penerimaan
            DB::statement('DROP TABLE IF EXISTS summary_mart_penerimaan_temp;');
            DB::statement('CREATE TABLE summary_mart_penerimaan_temp LIKE summary_mart_penerimaan;');

            // 2. Agregasikan data dari tabel drm ke summary_mart_penerimaan_temp
            DB::statement("
                INSERT INTO summary_mart_penerimaan_temp (
                    thn_setor, bln_setor, jenis, fungsi, total_setor, total_transaksi, created_at, updated_at
                )
                SELECT 
                    thn_setor,
                    bln_setor,
                    UPPER(TRIM(COALESCE(jenis, ''))) AS jenis,
                    UPPER(TRIM(COALESCE(fungsi, ''))) AS fungsi,
                    COALESCE(SUM(jml_setor), 0) AS total_setor,
                    COUNT(*) AS total_transaksi,
                    '{$now}',
                    '{$now}'
                FROM drm
                GROUP BY thn_setor, bln_setor, UPPER(TRIM(COALESCE(jenis, ''))), UPPER(TRIM(COALESCE(fungsi, '')))
            ");

            // 3. Swap Table Atomik di MariaDB (Zero Downtime untuk Dashboard)
            DB::statement('DROP TABLE IF EXISTS summary_mart_penerimaan_old;');
            DB::statement('RENAME TABLE 
                summary_mart_penerimaan TO summary_mart_penerimaan_old,
                summary_mart_penerimaan_temp TO summary_mart_penerimaan;
            ');
            DB::statement('DROP TABLE IF EXISTS summary_mart_penerimaan_old;');

            $executionTime = round(microtime(true) - $startTime, 2);
            $this->info("   [OK] Summary Mart FULL berhasil diperbarui dalam {$executionTime} detik!");

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->warn('   [!] Swapping gagal ('.$e->getMessage().'), menjalankan fallback direct rebuild...');

            // Cleanup jika terjadi kegagalan saat swapping
            DB::statement('DROP TABLE IF EXISTS summary_mart_penerimaan_temp;');
            DB::statement('DROP TABLE IF EXISTS summary_mart_penerimaan_old;');

            try {
                $now = now()->toDateTimeString();
                DB::statement('TRUNCATE TABLE summary_mart_penerimaan;');
                DB::statement("
                    INSERT INTO summary_mart_penerimaan (
                        thn_setor, bln_setor, jenis, fungsi, total_setor, total_transaksi, created_at, updated_at
                    )
                    SELECT 
                        thn_setor, 
                        bln_setor, 
                        UPPER(TRIM(COALESCE(jenis, ''))), 
                        UPPER(TRIM(COALESCE(fungsi, ''))), 
                        COALESCE(SUM(jml_setor), 0), 
                        COUNT(*), 
                        '{$now}', 
                        '{$now}'
                    FROM drm
                    GROUP BY thn_setor, bln_setor, UPPER(TRIM(COALESCE(jenis, ''))), UPPER(TRIM(COALESCE(fungsi, '')))
                ");

                return Command::SUCCESS;
            } catch (Exception $fallbackEx) {
                $this->error('   [ERROR] Gagal rebuild summary mart: '.$fallbackEx->getMessage());

                return Command::FAILURE;
            }
        }
    }

    /**
     * Partial Rebuild berdasarkan filter Periode (Tahun / Bulan)
     */
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

            // Hapus rekapitulasi pada periode terpilih saja
            DB::statement("DELETE FROM summary_mart_penerimaan{$whereSql};");

            // Rekap ulang periode terpilih dari drm
            DB::statement("
                INSERT INTO summary_mart_penerimaan (
                    thn_setor, bln_setor, jenis, fungsi, total_setor, total_transaksi, created_at, updated_at
                )
                SELECT 
                    thn_setor,
                    bln_setor,
                    UPPER(TRIM(COALESCE(jenis, ''))) AS jenis,
                    UPPER(TRIM(COALESCE(fungsi, ''))) AS fungsi,
                    COALESCE(SUM(jml_setor), 0) AS total_setor,
                    COUNT(*) AS total_transaksi,
                    '{$now}',
                    '{$now}'
                FROM drm
                {$whereSql}
                GROUP BY thn_setor, bln_setor, UPPER(TRIM(COALESCE(jenis, ''))), UPPER(TRIM(COALESCE(fungsi, '')))
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
