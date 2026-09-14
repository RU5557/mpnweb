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
        // 1. Ambil Filter Bulan & Tahun dari Request (Default: Bulan & Tahun saat ini)
        $thnIni = (int) $request->input('tahun', date('Y'));
        $blnIni = (int) $request->input('bulan', date('n'));
        $thnLalu = $thnIni - 1;

        // -------------------------------------------------------------
        // QUERY DATA TARGET & ROLLING TEXT DARI DATABASE
        // -------------------------------------------------------------
        
        // Ambil Target Tahunan dari DB berdasarkan tahun filter
        $target = Target::where('tahun', $thnIni)->first();

        // Ambil Rolling Text Harian Terbaru dari DB
        $rollingText = RollingText::latest('tanggal')->first();

        // -------------------------------------------------------------
        // QUERY CARD UTAMA (KUMULATIF S.D. BULAN FILTER)
        // -------------------------------------------------------------

        // Realisasi Saat Ini: Jan s.d. Bulan Filter
        $penerimaanSaatIni = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->sum('total_setor');

        // Realisasi Bulan Lalu: Jan s.d. (Bulan Filter - 1)
        $penerimaanBlnLalu = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<', $blnIni)
            ->sum('total_setor');

        // Realisasi Tahun Lalu: Jan s.d. Bulan Filter Tahun Sebelumnya
        $penerimaanThnLalu = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnLalu)
            ->where('bln_setor', '<=', $blnIni)
            ->sum('total_setor');

        // -------------------------------------------------------------
        // QUERY CARD KINERJA JENIS (KUMULATIF S.D. BULAN FILTER)
        // -------------------------------------------------------------

        // 1. PPM
        $realisasiPPM = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->sum('total_setor');

        // 2. PKM (Seluruh varian PKM)
        $realisasiPKM = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->whereIn('jenis', ['PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA'])
            ->sum('total_setor');

        // 3. PBP
        $realisasiPBP = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PBP')
            ->sum('total_setor');

        // -------------------------------------------------------------
        // QUERY CARD KINERJA FUNGSI (KUMULATIF S.D. BULAN FILTER)
        // -------------------------------------------------------------

        // 4. PKM Pengawasan (akt pengawasan + lainnya + wra pengawasan)
        $realisasiPengawasan = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->whereIn('fungsi', ['akt pengawasan', 'lainnya', 'wra pengawasan'])
            ->sum('total_setor');

        // 5. PKM Pemeriksaan (akt pemeriksaan)
        $realisasiPemeriksaan = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('fungsi', 'akt pemeriksaan')
            ->sum('total_setor');

        // 6. PKM Penagihan (akt penagihan)
        $realisasiPenagihan = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('fungsi', 'akt penagihan')
            ->sum('total_setor');
        
        // Capain Kantor
        $targetKantor = $target->target_kantor ?? 0;
        $capaianKantor = $targetKantor > 0 ? ($penerimaanSaatIni / $targetKantor) * 100 : 0;

        // Realisasi Kinerja Jenis - TAHUN LALU (s.d. Bulan yang sama)
        $realisasiPPMLalu = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnLalu)->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')->sum('total_setor');

        $realisasiPKMLalu = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnLalu)->where('bln_setor', '<=', $blnIni)
            ->whereIn('jenis', ['PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA'])->sum('total_setor');

        // Realisasi Kinerja Fungsi - TAHUN LALU (s.d. Bulan yang sama)
        $realisasiPengawasanLalu = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnLalu)->where('bln_setor', '<=', $blnIni)
            ->whereIn('fungsi', ['akt pengawasan', 'lainnya', 'wra pengawasan'])->sum('total_setor');

        $realisasiPemeriksaanLalu = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnLalu)->where('bln_setor', '<=', $blnIni)
            ->where('fungsi', 'akt pemeriksaan')->sum('total_setor');

        $realisasiPenagihanLalu = DB::table('summary_mart_penerimaan')
            ->where('thn_setor', $thnLalu)->where('bln_setor', '<=', $blnIni)
            ->where('fungsi', 'akt penagihan')->sum('total_setor');

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