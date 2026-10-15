<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class RebuildSummaryPkm extends Command
{
    protected $signature = 'summary:rebuild-pkm';

    protected $description = 'Membangun ulang tabel summary_pkm dari tabel drm';

    public function handle()
    {
        $this->info('Memulai rebuild summary_pkm...');

        try {
            DB::statement('DROP TABLE IF EXISTS summary_pkm_temp');
            DB::statement('DROP TABLE IF EXISTS summary_pkm_old');

            DB::statement('CREATE TABLE summary_pkm_temp LIKE summary_pkm');

            $this->info('Mengagregasi data dari tabel drm...');
            DB::statement('
                INSERT INTO summary_pkm_temp (thn_setor, bln_setor, npwp15, fungsi, flag_skp, total_setor, total_transaksi, created_at, updated_at)
                SELECT 
                    thn_setor,
                    bln_setor,
                    npwp15,
                    fungsi,
                    flag_skp,
                    COALESCE(SUM(jml_setor), 0) as total_setor,
                    COUNT(id) as total_transaksi,
                    NOW() as created_at,
                    NOW() as updated_at
                FROM drm
                WHERE thn_setor IS NOT NULL AND bln_setor IS NOT NULL
                GROUP BY thn_setor, bln_setor, npwp15, fungsi, flag_skp
            ');

            $this->info('Melakukan atomic table swap...');
            DB::statement('RENAME TABLE summary_pkm TO summary_pkm_old, summary_pkm_temp TO summary_pkm');
            DB::statement('DROP TABLE IF EXISTS summary_pkm_old');

            $this->info('Sukses memperbarui data summary_pkm!');
        } catch (Throwable $e) {
            $this->error('Terjadi kesalahan: '.$e->getMessage());
            DB::statement('DROP TABLE IF EXISTS summary_pkm_temp');
            DB::statement('DROP TABLE IF EXISTS summary_pkm_old');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
