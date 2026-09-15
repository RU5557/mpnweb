<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PkmPengawasanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $seksiFilter = $request->input('seksi');

        // Parameter Sorting (Default berdasarkan nama_seksi ASC)
        $sortColumn = $request->input('sort', 'nama_seksi');
        $sortDirection = $request->input('direction', 'asc');

        // Mapping kolom yang diizinkan untuk di-sort
        $allowedSorts = [
            'nama_seksi' => DB::raw("COALESCE(s.nama, 'Unassign')"),
            'nama_ar' => DB::raw("COALESCE(p.nama, 'Unassign')"),
            'total_akt_pengawasan' => 'total_akt_pengawasan',
            'total_lainnya' => 'total_lainnya',
            'total_wra_pengawasan' => 'total_wra_pengawasan',
            'total_pkm_pengawasan' => 'total_pkm_pengawasan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? DB::raw("COALESCE(s.nama, 'Unassign')");
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        // Subquery pegawai difilter berdasarkan tahun terpilih
        $subPegawai = DB::table('pegawai')
            ->where('tahun', $tahun);

        $query = DB::table('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoinSub($subPegawai, 'p', function ($join) {
                $join->on('mw.nip_ar', '=', 'p.nip');
            })
            ->leftJoin('seksi as s', 'p.seksi', '=', 's.id')
            ->select(
                DB::raw("COALESCE(mw.nip_ar, 'Unassign') as nip_ar"),
                DB::raw("COALESCE(p.nama, 'Unassign') as nama_ar"),
                DB::raw("COALESCE(s.nama, 'Unassign') as nama_seksi"),
                DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt pengawasan' THEN dt.jml_setor ELSE 0 END) as total_akt_pengawasan"),
                DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'lainnya' THEN dt.jml_setor ELSE 0 END) as total_lainnya"),
                DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'wra pengawasan' THEN dt.jml_setor ELSE 0 END) as total_wra_pengawasan"),
                DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) IN ('akt pengawasan', 'lainnya', 'wra pengawasan') THEN dt.jml_setor ELSE 0 END) as total_pkm_pengawasan")
            )
            ->whereIn(DB::raw('LOWER(dt.fungsi)'), ['akt pengawasan', 'lainnya', 'wra pengawasan'])
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->where('dt.thn_setor', $tahun)
            ->when($seksiFilter, function ($q, $seksi) {
                if ($seksi === 'Unassign') {
                    return $q->whereNull('s.nama');
                }
                return $q->where('s.nama', $seksi);
            })
            ->groupBy(
                DB::raw("COALESCE(mw.nip_ar, 'Unassign')"),
                DB::raw("COALESCE(p.nama, 'Unassign')"),
                DB::raw("COALESCE(s.nama, 'Unassign')")
            );

        // Terapkan Order By berdasarkan sort parameter
        $pkmData = $query->orderBy($sortBy, $sortDir)
            // Secondary sort agar tampilan tetap konsisten saat sorting nama_seksi
            ->when($sortColumn === 'nama_seksi', function ($q) use ($sortDir) {
                return $q->orderBy(DB::raw("COALESCE(p.nama, 'Unassign')"), 'asc');
            })
            ->get();

        // Ambil seksi yang mengandung kata 'Pengawasan'
        $daftarSeksi = DB::table('seksi')
            ->where('nama', 'LIKE', '%Pengawasan%')
            ->orderBy('nama', 'asc')
            ->pluck('nama')
            ->toArray();

        $daftarSeksi[] = 'Unassign';

        return view('penerimaan.pkmpengawasan', compact('pkmData', 'daftarSeksi', 'sortColumn', 'sortDirection'));
    }
}