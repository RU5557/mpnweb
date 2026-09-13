<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RebuildSummaryMart extends Command
{
    /**
     * Nama dan signature command terminal.
     */
    protected $signature = 'summary:rebuild {--tahun= : Tahun spesifik yang ingin di-rebuild (Opsional)}';

    /**
     * Deskripsi singkat command.
     */
    protected $description = 'Merekap ulang data dari detil_transaksi_wp ke summary_penerimaan_kpp';

    /**
     * Eksekusi logika command.
     */
    public function handle()
    {
        $tahun = $this->option('tahun');

        $this->info('Memulai kalkulasi dan rekapitulasi data summary mart...');

        DB::beginTransaction();

        try {
            // 1. Bersihkan data summary lama
            $deleteQuery = DB::table('summary_penerimaan_kpp');
            if ($tahun) {
                $deleteQuery->where('thn_setor', $tahun);
                $this->line("Menghapus data summary untuk tahun: {$tahun}");
            } else {
                $deleteQuery->truncate();
                $this->line("Menghapus seluruh data summary mart...");
            }

            // 2. Agregasi data dari detil_transaksi_wp ke summary_penerimaan_kpp
            $selectQuery = DB::table('detil_transaksi_wp')
                ->select(
                    'thn_setor',
                    'bln_setor',
                    DB::raw("COALESCE(kpp_adm, '000') as kpp_adm"),
                    DB::raw("COALESCE(kd_map, '') as kd_map"),
                    DB::raw("COALESCE(kd_bayar, '') as kd_bayar"),
                    DB::raw("SUM(jml_setor) as total_setor"),
                    DB::raw("COUNT(id) as total_transaksi")
                )
                ->whereNotNull('thn_setor')
                ->whereNotNull('bln_setor');

            if ($tahun) {
                $selectQuery->where('thn_setor', $tahun);
            }

            $selectQuery->groupBy('thn_setor', 'bln_setor', 'kpp_adm', 'kd_map', 'kd_bayar');

            // 3. Insert hasil kalkulasi ke tabel summary_penerimaan_kpp
            DB::statement("
                INSERT INTO summary_penerimaan_kpp (thn_setor, bln_setor, kpp_adm, kd_map, kd_bayar, total_setor, total_transaksi)
                " . $selectQuery->toSql() . "
            ", $selectQuery->getBindings());

            DB::commit();

            $this->info('Sukses! Tabel summary_penerimaan_kpp berhasil ter-update.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Gagal melakukan rebuild summary: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}