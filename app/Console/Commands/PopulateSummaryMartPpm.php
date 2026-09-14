<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PopulateSummaryMartPpm extends Command
{
    /**
     * Perintah CLI
     */
    protected $signature = 'app:populate-summary-mart-ppm {--tahun= : Tahun spesifik yang ingin di-rekap}';

    /**
     * Deskripsi Perintah
     */
    protected $description = 'Melakukan ETL/Rekapitulasi data transaksi ke tabel summary_mart_ppm (Opsi A - Subquery KDMAP)';

    public function handle()
    {
        $this->info('Memulai proses ETL summary_mart_ppm (Opsi A)...');
        $startTime = microtime(true);

        $tahun = $this->option('tahun');

        // 1. Bersihkan Data Target (Delete per Tahun atau Full Truncate)
        if ($tahun) {
            $this->info("Menghapus data lama di summary_mart_ppm untuk tahun {$tahun}...");
            DB::table('summary_mart_ppm')->where('thn_setor', $tahun)->delete();
        } else {
            $this->info("Menghapus seluruh data lama di summary_mart_ppm...");
            DB::table('summary_mart_ppm')->truncate();
        }

        // 2. Subquery Deduplikasi KDMAP (Opsi A)
        // Menghasilkan derived table berisi kd_map unik + jenis_pajak di RAM
        $kdmapUnique = DB::table('kdmap')
            ->select('kd_map', DB::raw('MAX(jenis_pajak) as jenis_pajak'))
            ->groupBy('kd_map');

        // 3. Query Utama ETL
        $query = DB::table('detil_transaksi_wp as dt')
            // JOIN ke Masterfile WP via npwp15
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            // JOIN ke KLU via klu -> kd_klu
            ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
            // JOIN ke Subquery Derived Table KDMAP (Opsi A)
            ->leftJoinSub($kdmapUnique, 'km', function ($join) {
                $join->on('dt.kd_map', '=', 'km.kd_map');
            })
            ->where('dt.jenis', 'PPM')
            ->when($tahun, function ($q) use ($tahun) {
                return $q->where('dt.thn_setor', $tahun);
            })
            ->select(
                'dt.thn_setor',
                'dt.bln_setor',
                'dt.jenis',
                'dt.npwp15 as npwp', // Di-alias ke npwp sesuai tabel summary_mart_ppm
                'dt.nama_wp',
                'k.kd_kategori',
                'k.nm_kategori',
                'dt.kd_map',
                'km.jenis_pajak',
                DB::raw('SUM(dt.jml_setor) as jml_setor'),
                DB::raw('COUNT(dt.id) as total_transaksi'),
                DB::raw('NOW() as created_at'),
                DB::raw('NOW() as updated_at')
            )
            ->groupBy(
                'dt.thn_setor',
                'dt.bln_setor',
                'dt.jenis',
                'dt.npwp15',
                'dt.nama_wp',
                'k.kd_kategori',
                'k.nm_kategori',
                'dt.kd_map',
                'km.jenis_pajak'
            );

        // 4. Eksekusi Batch Insert Into Select
        $this->info('Mengagregasi dan memasukkan data baru...');

        $bindings = $query->getBindings();
        $sql = $query->toSql();

        DB::statement("
            INSERT INTO summary_mart_ppm (
                thn_setor, bln_setor, jenis, npwp, nama_wp, 
                kd_kategori, nm_kategori, kd_map, jenis_pajak, 
                jml_setor, total_transaksi, created_at, updated_at
            )
            {$sql}
        ", $bindings);

        $executionTime = round(microtime(true) - $startTime, 2);
        $this->info("ETL Opsi A Selesai dalam {$executionTime} detik!");

        return Command::SUCCESS;
    }
}