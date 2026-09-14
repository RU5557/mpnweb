<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
}