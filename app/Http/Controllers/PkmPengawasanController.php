<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Exports\DetilTransaksiExport;
use Maatwebsite\Excel\Facades\Excel;

class PkmPengawasanController extends Controller
{
public function index(Request $request)
{
    $bulan = (int) $request->input('bulan', date('m'));
    $tahun = (int) $request->input('tahun', date('Y'));
    $seksiFilter = $request->input('seksi', '');
    $sortColumn = $request->input('sort', 'nama_seksi');
    $sortDirection = $request->input('direction', 'asc');

    // Buat Key unik berdasarkan parameter input
    $cacheKey = "pkm_pengawasan_{$tahun}_{$bulan}_{$seksiFilter}_{$sortColumn}_{$sortDirection}";

    // Simpan hasil query di RAM Cache selama 10 Menit (600 detik)
    $pkmData = Cache::remember($cacheKey, 600, function () use ($bulan, $tahun, $seksiFilter, $sortColumn, $sortDirection) {
        
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

        $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

        return DB::table('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoinSub($subPegawai, 'p', function ($join) {
                $join->on('mw.nip_ar', '=', 'p.nip');
            })
            ->leftJoin('seksi as s', 'p.seksi', '=', 's.id')
            ->select(
                DB::raw("COALESCE(mw.nip_ar, 'Unassign') as nip_ar"),
                DB::raw("COALESCE(p.nama, 'Unassign') as nama_ar"),
                DB::raw("COALESCE(s.nama, 'Unassign') as nama_seksi"),
                DB::raw("SUM(CASE WHEN dt.fungsi LIKE 'akt%' THEN dt.jml_setor ELSE 0 END) as total_akt_pengawasan"),
                DB::raw("SUM(CASE WHEN dt.fungsi LIKE 'lain%' THEN dt.jml_setor ELSE 0 END) as total_lainnya"),
                DB::raw("SUM(CASE WHEN dt.fungsi LIKE 'wra%' THEN dt.jml_setor ELSE 0 END) as total_wra_pengawasan"),
                DB::raw("SUM(dt.jml_setor) as total_pkm_pengawasan")
            )
            ->whereIn('dt.fungsi', ['Akt Pengawasan', 'Lainnya', 'WRA Pengawasan', 'akt pengawasan', 'lainnya', 'wra pengawasan'])
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
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
            )
            ->orderBy($sortBy, $sortDir)
            ->when($sortColumn === 'nama_seksi', function ($q) use ($sortDir) {
                return $q->orderBy(DB::raw("COALESCE(p.nama, 'Unassign')"), 'asc');
            })
            ->get();
    });

    $daftarSeksi = Cache::remember('daftar_seksi_pengawasan', 3600, function () {
        $seksi = DB::table('seksi')
            ->where('nama', 'LIKE', '%Pengawasan%')
            ->orderBy('nama', 'asc')
            ->pluck('nama')
            ->toArray();
        $seksi[] = 'Unassign';
        return $seksi;
    });

    return view('penerimaan.pkmpengawasan', compact('pkmData', 'daftarSeksi', 'sortColumn', 'sortDirection'));
}

/**
     * Handle Export Excel Detil Transaksi
     */
    public function exportDetil(Request $request)
    {
        // Tangkap filter dari Request Form UI
        $filters = [
            'thn_setor' => $request->input('thn_setor', date('Y')),
            'bln_setor' => $request->input('bln_setor'),
            'fungsi'    => $request->input('fungsi', 'PENGAWASAN'), // Sesuaikan: PENGAWASAN, PEMERIKSAAN, atau PENAGIHAN
            'jenis'     => $request->input('jenis'),
            'nip_ar'    => $request->input('nip_ar'),
        ];

        $namaFile = 'Detil_Transaksi_' . $filters['fungsi'] . '_' . date('Ymd_His') . '.xlsx';

        return Excel::download(new DetilTransaksiExport($filters), $namaFile);
    }
}