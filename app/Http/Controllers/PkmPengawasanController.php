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

$data = DB::table('detil_transaksi_wp as dt')
    ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
    ->leftJoin('pegawai as p', function($join) use ($thnIni) {
        $join->on('mw.nip_ar', '=', 'p.nip')
             ->where('p.tahun', '=', $thnIni);
    })
    ->leftJoin('seksi as s', 'p.seksi', '=', 's.id')
    ->whereIn(DB::raw('LOWER(dt.fungsi)'), ['akt pengawasan', 'lainnya', 'wra pengawasan'])
    ->where('dt.thn_setor', $thnIni)
    ->whereBetween('dt.bln_setor', [1, $blnIni])
    ->selectRaw("
        COALESCE(mw.nip_ar, 'Unassign') as nip_ar,
        COALESCE(p.nama, 'Unassign') as nama_ar,
        COALESCE(s.nama, 'Unassign') as nama_seksi,
        SUM(CASE WHEN LOWER(dt.fungsi) = 'akt pengawasan' THEN dt.jml_setor ELSE 0 END) as total_akt_pengawasan,
        SUM(CASE WHEN LOWER(dt.fungsi) = 'lainnya' THEN dt.jml_setor ELSE 0 END) as total_lainnya,
        SUM(CASE WHEN LOWER(dt.fungsi) = 'wra pengawasan' THEN dt.jml_setor ELSE 0 END) as total_wra_pengawasan,
        SUM(dt.jml_setor) as total_pkm_pengawasan
    ")
    ->groupBy(
        DB::raw("COALESCE(mw.nip_ar, 'Unassign')"),
        DB::raw("COALESCE(p.nama, 'Unassign')"),
        DB::raw("COALESCE(s.nama, 'Unassign')")
    )
    ->orderBy(DB::raw("COALESCE(s.nama, 'Unassign')"), 'asc')
    ->orderBy(DB::raw("COALESCE(p.nama, 'Unassign')"), 'asc')
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