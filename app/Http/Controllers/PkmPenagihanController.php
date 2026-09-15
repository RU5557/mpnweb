<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PkmPenagihanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $dspcFilter = $request->input('dspc_filter');

        // Parameter Sorting (Default: nip_jspn ASC)
        $sortColumn = $request->input('sort', 'nip_jspn');
        $sortDirection = $request->input('direction', 'asc');

        // Mapping kolom yang diizinkan untuk di-sort
        $allowedSorts = [
            'nip_jspn' => DB::raw("COALESCE(mw.nip_js, 'Unassign')"),
            'nama_jspn' => DB::raw("COALESCE(p.nama, 'Unassign')"),
            'flag_skp' => DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC')"),
            'akt_penagihan' => 'akt_penagihan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? DB::raw("COALESCE(mw.nip_js, 'Unassign')");
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        // Subquery pegawai difilter berdasarkan tahun terpilih
        $subPegawai = DB::table('pegawai')
            ->where('tahun', $tahun);

        $pkmData = DB::table('detil_transaksi_wp as dt')
            // Join ke masterfile_wp untuk mendapatkan NIP JSPN (nip_js)
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            // Join ke pegawai berdasarkan NIP JSPN
            ->leftJoinSub($subPegawai, 'p', function ($join) {
                $join->on('mw.nip_js', '=', 'p.nip');
            })
            ->select(
                DB::raw("COALESCE(mw.nip_js, 'Unassign') as nip_jspn"),
                DB::raw("COALESCE(p.nama, 'Unassign') as nama_jspn"),
                DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC') as flag_skp"),
                DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt penagihan' THEN dt.jml_setor ELSE 0 END) as akt_penagihan")
            )
            ->where(DB::raw('LOWER(dt.fungsi)'), 'akt penagihan')
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->where('dt.thn_setor', $tahun)
            // Filter Khusus DSPC / NON-DSPC
            ->when($dspcFilter, function ($query, $flag) {
                if ($flag === 'DSPC') {
                    return $query->where(DB::raw("UPPER(TRIM(dt.flag_skp))"), 'DSPC');
                } elseif ($flag === 'NON-DSPC') {
                    return $query->where(function ($q) {
                        $q->where(DB::raw("UPPER(TRIM(dt.flag_skp))"), '!=', 'DSPC')
                          ->orWhereNull('dt.flag_skp');
                    });
                }
            })
            ->groupBy(
                DB::raw("COALESCE(mw.nip_js, 'Unassign')"),
                DB::raw("COALESCE(p.nama, 'Unassign')"),
                DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC')")
            )
            ->orderBy($sortBy, $sortDir)
            ->get(); // Tanpa paginasi, mengambil seluruh data hasil filter

        return view('penerimaan.pkmpenagihan', compact('pkmData', 'sortColumn', 'sortDirection'));
    }
}