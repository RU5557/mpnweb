<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RebuildSummaryPenjagaan extends Command
{
    /**
     * Nama command Artisan.
     */
    protected $signature = 'summary:rebuild-penjagaan';

    /**
     * Deskripsi command.
     */
    protected $description = 'Membangun ulang tabel summary_penjagaan dari tabel drm';

    public function handle()
    {
        $this->info('Memulai proses rebuild summary penjagaan...');

        try {
            // 1. Bersihkan tabel temporary lama jika sisa run sebelumnya
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_temp');
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_old');

            // 2. Buat tabel temp berstruktur sama
            DB::statement('CREATE TABLE summary_penjagaan_temp LIKE summary_penjagaan');

            // 3. Populate data dari tabel drm ke tabel temp
            $this->info('Mengagregasi data dari tabel drm...');
            DB::statement('
                INSERT INTO summary_penjagaan_temp (thn_setor, bln_setor, tgl_setor, fungsi, total_setor, total_transaksi, created_at, updated_at)
                SELECT 
                    thn_setor,
                    bln_setor,
                    tgl_setor,
                    fungsi,
                    COALESCE(SUM(jml_setor), 0) as total_setor,
                    COUNT(id) as total_transaksi,
                    NOW() as created_at,
                    NOW() as updated_at
                FROM drm
                WHERE tgl_setor IS NOT NULL
                GROUP BY thn_setor, bln_setor, tgl_setor, fungsi
            ');

            // 4. Lakukan Atomic Table Swap (Zero Downtime)
            $this->info('Melakukan swap tabel...');
            DB::statement('RENAME TABLE summary_penjagaan TO summary_penjagaan_old, summary_penjagaan_temp TO summary_penjagaan');
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_old');

            $this->info('Sukses memperbarui data summary_penjagaan!');
        } catch (Throwable $e) {
            $this->error('Terjadi kesalahan saat rebuild summary: '.$e->getMessage());

            // Clean up jika gagal
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_temp');
            DB::statement('DROP TABLE IF EXISTS summary_penjagaan_old');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
