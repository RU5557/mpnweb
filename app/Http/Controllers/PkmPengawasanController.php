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

        // Subquery pegawai difilter berdasarkan tahun terpilih
        $subPegawai = DB::table('pegawai')
            ->where('tahun', $tahun);

        $pkmData = DB::table('detil_transaksi_wp as dt')
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
            ->when($seksiFilter, function ($query, $seksi) {
                if ($seksi === 'Unassign') {
                    return $query->whereNull('s.nama');
                }
                return $query->where('s.nama', $seksi);
            })
            ->groupBy(
                DB::raw("COALESCE(mw.nip_ar, 'Unassign')"),
                DB::raw("COALESCE(p.nama, 'Unassign')"),
                DB::raw("COALESCE(s.nama, 'Unassign')")
            )
            ->orderBy(DB::raw("COALESCE(s.nama, 'Unassign')"), 'asc')
            ->orderBy(DB::raw("COALESCE(p.nama, 'Unassign')"), 'asc')
            ->get();

        // Perbaikan 1: Ambil daftar nama_seksi unik langsung dari hasil query $pkmData
        $daftarSeksi = $pkmData->pluck('nama_seksi')->unique()->filter()->values();

        return view('penerimaan.pkmpengawasan', compact('pkmData', 'daftarSeksi'));
    }
}