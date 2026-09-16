<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\Target;
use App\Models\RollingText;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Filter Bulan & Tahun secara dinamis dari Request
        $thnIni = (int) $request->input('tahun', date('Y'));
        $blnIni = (int) $request->input('bulan', date('n'));
        $thnLalu = $thnIni - 1;

        // 2. Query Target & RollingText
        $target = Target::where('tahun', $thnIni)->first();
        $rollingText = RollingText::latest('tanggal')->first();

        // 3. Cache Query Single-Pass Dashboard (Dinamis berdasarkan Tahun & Bulan)
        $cacheKey = "dashboard_summary_{$thnIni}_{$blnIni}";

        $penerimaanData = Cache::remember($cacheKey, 600, function () use ($thnIni, $thnLalu, $blnIni) {
            return DB::table('summary_mart_penerimaan')
                ->whereIn('thn_setor', [$thnIni, $thnLalu])
                ->where('bln_setor', '<=', $blnIni)
                ->selectRaw("
                    -- Realisasi Utama Tahun Ini
                    SUM(CASE WHEN thn_setor = {$thnIni} THEN total_setor ELSE 0 END) as penerimaanSaatIni,
                    SUM(CASE WHEN thn_setor = {$thnIni} AND bln_setor < {$blnIni} THEN total_setor ELSE 0 END) as penerimaanBlnLalu,
                    
                    -- Realisasi Utama Tahun Lalu
                    SUM(CASE WHEN thn_setor = {$thnLalu} THEN total_setor ELSE 0 END) as penerimaanThnLalu,

                    -- Kinerja Jenis (Tahun Ini)
                    SUM(CASE WHEN thn_setor = {$thnIni} AND jenis = 'PPM' THEN total_setor ELSE 0 END) as realisasiPPM,
                    SUM(CASE WHEN thn_setor = {$thnIni} AND jenis IN ('PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA') THEN total_setor ELSE 0 END) as realisasiPKM,
                    SUM(CASE WHEN thn_setor = {$thnIni} AND jenis = 'PBP' THEN total_setor ELSE 0 END) as realisasiPBP,

                    -- Kinerja Fungsi (Tahun Ini)
                    SUM(CASE WHEN thn_setor = {$thnIni} AND fungsi IN ('akt pengawasan', 'lainnya', 'wra pengawasan') THEN total_setor ELSE 0 END) as realisasiPengawasan,
                    SUM(CASE WHEN thn_setor = {$thnIni} AND fungsi = 'akt pemeriksaan' THEN total_setor ELSE 0 END) as realisasiPemeriksaan,
                    SUM(CASE WHEN thn_setor = {$thnIni} AND fungsi = 'akt penagihan' THEN total_setor ELSE 0 END) as realisasiPenagihan,

                    -- Kinerja Jenis (Tahun Lalu)
                    SUM(CASE WHEN thn_setor = {$thnLalu} AND jenis = 'PPM' THEN total_setor ELSE 0 END) as realisasiPPMLalu,
                    SUM(CASE WHEN thn_setor = {$thnLalu} AND jenis IN ('PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA') THEN total_setor ELSE 0 END) as realisasiPKMLalu,

                    -- Kinerja Fungsi (Tahun Lalu)
                    SUM(CASE WHEN thn_setor = {$thnLalu} AND fungsi IN ('akt pengawasan', 'lainnya', 'wra pengawasan') THEN total_setor ELSE 0 END) as realisasiPengawasanLalu,
                    SUM(CASE WHEN thn_setor = {$thnLalu} AND fungsi = 'akt pemeriksaan' THEN total_setor ELSE 0 END) as realisasiPemeriksaanLalu,
                    SUM(CASE WHEN thn_setor = {$thnLalu} AND fungsi = 'akt penagihan' THEN total_setor ELSE 0 END) as realisasiPenagihanLalu
                ")
                ->first();
        });

        // Assign nilai variabel
        $penerimaanSaatIni   = $penerimaanData->penerimaanSaatIni ?? 0;
        $penerimaanBlnLalu   = $penerimaanData->penerimaanBlnLalu ?? 0;
        $penerimaanThnLalu   = $penerimaanData->penerimaanThnLalu ?? 0;

        $realisasiPPM        = $penerimaanData->realisasiPPM ?? 0;
        $realisasiPKM        = $penerimaanData->realisasiPKM ?? 0;
        $realisasiPBP        = $penerimaanData->realisasiPBP ?? 0;

        $realisasiPengawasan = $penerimaanData->realisasiPengawasan ?? 0;
        $realisasiPemeriksaan= $penerimaanData->realisasiPemeriksaan ?? 0;
        $realisasiPenagihan  = $penerimaanData->realisasiPenagihan ?? 0;

        $realisasiPPMLalu    = $penerimaanData->realisasiPPMLalu ?? 0;
        $realisasiPKMLalu    = $penerimaanData->realisasiPKMLalu ?? 0;

        $realisasiPengawasanLalu  = $penerimaanData->realisasiPengawasanLalu ?? 0;
        $realisasiPemeriksaanLalu = $penerimaanData->realisasiPemeriksaanLalu ?? 0;
        $realisasiPenagihanLalu   = $penerimaanData->realisasiPenagihanLalu ?? 0;

        // Capaian Kantor
        $targetKantor  = $target->target_kantor ?? 0;
        $capaianKantor = $targetKantor > 0 ? ($penerimaanSaatIni / $targetKantor) * 100 : 0;

        return view('penerimaan.dashboard', compact(
            'thnIni',
            'blnIni',
            'target',
            'rollingText',
            'capaianKantor',
            'penerimaanSaatIni',
            'penerimaanBlnLalu',
            'penerimaanThnLalu',
            'realisasiPPM',
            'realisasiPKM',
            'realisasiPBP',
            'realisasiPengawasan',
            'realisasiPemeriksaan',
            'realisasiPenagihan',
            'realisasiPPMLalu',
            'realisasiPKMLalu',
            'realisasiPengawasanLalu',
            'realisasiPemeriksaanLalu',
            'realisasiPenagihanLalu'
        ));
    }

/**
     * Handle Export CSV Detil Transaksi Dashboard (Streaming & Hemat Memory)
     */
    public function exportDetil(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('n'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $filename = "Export_Detil_Transaksi_Dashboard_{$tahun}_{$bulan}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function () use ($tahun, $bulan) {
            // Menaikkan limit eksekusi waktu untuk data berukuran sangat besar
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            
            // Output BOM UTF-8
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Menggunakan cursor() untuk mengambil baris per baris dari DB (Memory Efficient)
            $query = DB::table('detil_transaksi_wp as dt')
                ->where('dt.thn_setor', $tahun)
                ->where('dt.bln_setor', '<=', $bulan)
                ->orderBy('dt.bln_setor', 'asc');

            $isHeaderWritten = false;

            foreach ($query->cursor() as $row) {
                $rowArray = (array) $row;

                // Tulis Header Kolom secara Dinamis dari baris pertama
                if (!$isHeaderWritten) {
                    fputcsv($file, array_keys($rowArray));
                    $isHeaderWritten = true;
                }

                // // Format NPWP agar tidak dikonversi ke Scientific di Excel
                // if (isset($rowArray['npwp15'])) {
                //     $rowArray['npwp15'] = "'{$rowArray['npwp15']}";
                // }

                fputcsv($file, $rowArray);

                // Flush buffer secara berkala
                if (ob_get_level() > 0) {
                    ob_flush();
                }
                flush();
            }

            if (!$isHeaderWritten) {
                fputcsv($file, ['INFO']);
                fputcsv($file, ['Tidak ada data transaksi']);
            }

            fclose($file);
        }, 200, $headers);
    }
}