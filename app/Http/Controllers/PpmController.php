<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PpmController extends Controller
{
    public function index(Request $request)
    {
        $thnIni = $request->get('tahun', date('Y'));
        $blnIni = $request->get('bulan', date('m'));

        // 1. Top WP Utama
        $topWp = DB::table('summary_mart_ppm')
            ->select('nama_wp', DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->whereNotNull('nama_wp')
            ->where('nama_wp', '!=', '')
            ->groupBy('nama_wp')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 2. Top Kategori (Memastikan variabel $topKategori ada)
        $topKategori = DB::table('summary_mart_ppm')
            ->select('nm_kategori', DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->whereNotNull('nm_kategori')
            ->where('nm_kategori', '!=', '')
            ->groupBy('nm_kategori')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 3. Top Jenis Pajak
        $topJenisPajak = DB::table('summary_mart_ppm')
            ->select('jenis_pajak', DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->whereNotNull('jenis_pajak')
            ->where('jenis_pajak', '!=', '')
            ->groupBy('jenis_pajak')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 4. Top WP PPN DN (MAP 411211)
        $topWpPpnDn = DB::table('summary_mart_ppm')
            ->select('nama_wp', DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->whereNotNull('nama_wp')
            ->where('nama_wp', '!=', '')
            ->where('kd_map', '411211')
            ->groupBy('nama_wp')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 5. Top WP PPN Impor (MAP 411212)
        $topWpPpnImpor = DB::table('summary_mart_ppm')
            ->select('nama_wp', DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->whereNotNull('nama_wp')
            ->where('nama_wp', '!=', '')
            ->where('kd_map', '411212')
            ->groupBy('nama_wp')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 6. Top WP PPh Badan (MAP 411126)
        $topWpPphBadan = DB::table('summary_mart_ppm')
            ->select('nama_wp', DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->whereNotNull('nama_wp')
            ->where('nama_wp', '!=', '')
            ->where('kd_map', '411126')
            ->groupBy('nama_wp')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 7. Top WP PPh 21 (MAP 411122)
        $topWpPph21 = DB::table('summary_mart_ppm')
            ->select('nama_wp', DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $thnIni)
            ->where('bln_setor', '<=', $blnIni)
            ->where('jenis', 'PPM')
            ->whereNotNull('nama_wp')
            ->where('nama_wp', '!=', '')
            ->where('kd_map', '411122')
            ->groupBy('nama_wp')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return view('penerimaan.ppm', compact(
            'thnIni',
            'blnIni',
            'topWp',
            'topKategori',
            'topJenisPajak',
            'topWpPpnDn',
            'topWpPpnImpor',
            'topWpPphBadan',
            'topWpPph21'
        ));
    }

/**
     * Handle Export CSV Detil Transaksi PPM (Streaming & Hemat Memory)
     */
    public function exportDetil(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('n'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $filename = "Export_Detil_PPM_{$tahun}_{$bulan}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        return response()->stream(function () use ($tahun, $bulan) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            
            // Output BOM UTF-8
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Kolom CSV
            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'KD KLU', 'SEKTOR', 
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'JENIS', 'BULAN', 'TAHUN', 'JUMLAH SETOR'
            ]);

            // Query dengan Streaming Cursor
            $query = DB::table('detil_transaksi_wp as dt')
                ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
                ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
                ->select(
                    'dt.npwp15',
                    DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar') as nama_wp"),
                    DB::raw("COALESCE(mw.klu, '-') as kd_klu"),
                    DB::raw("COALESCE(k.nm_kategori, '-') as nm_kategori"),
                    'dt.kd_map',
                    'dt.kd_bayar',
                    'dt.fungsi',
                    'dt.jenis',
                    'dt.bln_setor',
                    'dt.thn_setor',
                    'dt.jml_setor'
                )
                ->where('dt.jenis', 'PPM')
                ->where('dt.thn_setor', $tahun)
                ->where('dt.bln_setor', '<=', $bulan)
                ->orderBy('mw.nama', 'asc')
                ->orderBy('dt.bln_setor', 'asc');

            $index = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $index++,
                    $row->npwp15,
                    $row->nama_wp,
                    $row->kd_klu,
                    $row->nm_kategori,
                    $row->kd_map,
                    $row->kd_bayar,
                    $row->fungsi,
                    $row->jenis,
                    $row->bln_setor,
                    $row->thn_setor,
                    $row->jml_setor
                ]);

                // Flush buffer secara berkala
                if ($index % 1000 === 0) {
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }
            }

            fclose($file);
        }, 200, $headers);
    }
}