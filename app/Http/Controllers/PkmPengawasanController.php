<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PkmPengawasanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $seksiFilter = $request->input('seksi');

        // Query data PKM Pengawasan per Seksi & AR
        $pkmData = DB::table('pkm_pengawasan')
            ->select(
                'nama_seksi',
                'nama_ar',
                DB::raw('SUM(akt_pengawasan) as total_akt_pengawasan'),
                DB::raw('SUM(pkm_lainnya) as total_lainnya'),
                DB::raw('SUM(wra_pengawasan) as total_wra_pengawasan'),
                DB::raw('SUM(akt_pengawasan + pkm_lainnya + wra_pengawasan) as total_pkm_pengawasan')
            )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($seksiFilter, function ($query, $seksi) {
                return $query->where('nama_seksi', $seksi);
            })
            ->groupBy('nama_seksi', 'nama_ar')
            ->orderBy('nama_seksi', 'asc')
            ->orderBy('total_pkm_pengawasan', 'desc')
            ->get();

        $daftarSeksi = DB::table('pkm_pengawasan')
            ->select('nama_seksi')
            ->distinct()
            ->orderBy('nama_seksi')
            ->pluck('nama_seksi');

        return view('penerimaan.pkmpengawasan', compact('pkmData', 'daftarSeksi'));
    }
}