<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PkmPemeriksaanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $search = $request->input('search');

        // Parameter Sorting
        $sortColumn = $request->input('sort', 'total_akt_pemeriksaan');
        $sortDirection = $request->input('direction', 'desc');

        // Mapping nama kolom yang diizinkan untuk di-sort
        $allowedSorts = [
            'npwp' => 'dt.npwp15',
            'nama_wp' => 'nama_wp',
            'kd_klu' => 'kd_klu',
            'nm_klu' => 'nm_klu',
            'total_akt_pemeriksaan' => 'total_akt_pemeriksaan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? 'total_akt_pemeriksaan';
        $sortDir = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $pkmData = DB::table('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
            ->select(
                'dt.npwp15',
                DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar') as nama_wp"),
                DB::raw("COALESCE(mw.klu, '-') as kd_klu"),
                DB::raw("COALESCE(k.nm_klu, '-') as nm_klu"),
                DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt pemeriksaan' THEN dt.jml_setor ELSE 0 END) as total_akt_pemeriksaan")
            )
            ->where(DB::raw('LOWER(dt.fungsi)'), 'akt pemeriksaan')
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->where('dt.thn_setor', $tahun)
            ->when($search, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('dt.npwp15', 'LIKE', "%{$keyword}%")
                      ->orWhere('mw.nama', 'LIKE', "%{$keyword}%")
                      ->orWhere('mw.klu', 'LIKE', "%{$keyword}%")
                      ->orWhere('k.nm_klu', 'LIKE', "%{$keyword}%");
                });
            })
            ->groupBy('dt.npwp15', 'mw.nama', 'mw.klu', 'k.nm_klu')
            ->orderBy($sortBy, $sortDir)
            ->paginate(10) // Paginasi diubah menjadi 10
            ->withQueryString();

        return view('penerimaan.pkmpemeriksaan', compact('pkmData', 'sortColumn', 'sortDirection'));
    }
}