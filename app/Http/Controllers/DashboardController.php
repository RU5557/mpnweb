<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Target;
use App\Models\RollingText;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil Filter Bulan & Tahun dari Request
        $thnIni = (int) $request->input('tahun', date('Y'));
        $blnIni = (int) $request->input('bulan', date('n'));
        $thnLalu = $thnIni - 1;

        // 2. Query Target & RollingText
        // Menggunakan optional() agar tidak error crash/memory exhausted jika target bernilai null
        $target = Target::where('tahun', $thnIni)->first();

        $rollingText = RollingText::latest('tanggal')->first();

        // 3. OPTIMASI QUERY: Gabungkan semua query aggregate menjadi 1 Query Single-Pass
        $penerimaanData = DB::table('summary_mart_penerimaan')
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

        // Assign nilai variabel dari hasil query agregat tunggal
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
}