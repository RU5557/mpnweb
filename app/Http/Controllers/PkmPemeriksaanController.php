<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PkmPemeriksaanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $seksiFilter = $request->input('seksi');

        // Query data PKM Pemeriksaan
        $pkmData = DB::table('pkm_pemeriksaan')
            ->select(
                'nama_seksi',
                'nama_fungsional',
                DB::raw('SUM(pkm_pemeriksaan) as total_pkm_pemeriksaan'),
                DB::raw('SUM(pkm_lainnya) as total_lainnya'),
                DB::raw('SUM(pkm_pemeriksaan + pkm_lainnya) as total_pkm')
            )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($seksiFilter, function ($query, $seksi) {
                return $query->where('nama_seksi', $seksi);
            })
            ->groupBy('nama_seksi', 'nama_fungsional')
            ->orderBy('nama_seksi', 'asc')
            ->orderBy('total_pkm', 'desc')
            ->get();

        $daftarSeksi = DB::table('pkm_pemeriksaan')
            ->select('nama_seksi')
            ->distinct()
            ->orderBy('nama_seksi')
            ->pluck('nama_seksi');

        return view('penerimaan.pkmpemeriksaan', compact('pkmData', 'daftarSeksi'));
    }
}