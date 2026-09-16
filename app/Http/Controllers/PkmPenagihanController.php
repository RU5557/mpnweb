<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PkmPenagihanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $dspcFilter = $request->input('dspc_filter', '');

        // Parameter Sorting (Default: nip_jspn ASC)
        $sortColumn = $request->input('sort', 'nip_jspn');
        $sortDirection = $request->input('direction', 'asc');

        // Mapping kolom yang diizinkan untuk di-sort
        $allowedSorts = [
            'nip_jspn'      => DB::raw("COALESCE(mw.nip_js, 'Unassign')"),
            'nama_jspn'     => DB::raw("COALESCE(p.nama, 'Unassign')"),
            'flag_skp'      => DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC')"),
            'akt_penagihan' => 'akt_penagihan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? DB::raw("COALESCE(mw.nip_js, 'Unassign')");
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        // Cache Key Unik berdasarkan parameter request
        $cacheKey = "pkm_penagihan_{$tahun}_{$bulan}_{$dspcFilter}_{$sortColumn}_{$sortDirection}";

        // Simpan ke Cache selama 10 Menit (600 detik)
        $pkmData = Cache::remember($cacheKey, 600, function () use ($bulan, $tahun, $dspcFilter, $sortBy, $sortDir) {
            
            $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

            return DB::table('detil_transaksi_wp as dt')
                ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
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
                ->where('dt.thn_setor', $tahun)
                ->whereBetween('dt.bln_setor', [1, $bulan])
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
                ->get();
        });

        return view('penerimaan.pkmpenagihan', compact('pkmData', 'sortColumn', 'sortDirection'));
    }

    /**
     * Handle Export CSV/Excel Detil Transaksi Penagihan
     */
    public function exportDetil(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $dspcFilter = $request->input('dspc_filter', '');

        $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

        $query = DB::table('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoinSub($subPegawai, 'p', function ($join) {
                $join->on('mw.nip_js', '=', 'p.nip');
            })
            ->select(
                'dt.npwp15',
                DB::raw("COALESCE(mw.nama, '-') as nama_wp"),
                DB::raw("COALESCE(mw.nip_js, 'Unassign') as nip_jspn"),
                DB::raw("COALESCE(p.nama, 'Unassign') as nama_jspn"),
                DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC') as flag_skp"),
                'dt.kd_map',
                'dt.kd_bayar',
                'dt.jml_setor',
                'dt.bln_setor',
                'dt.thn_setor',
                'dt.fungsi'
            )
            ->where(DB::raw('LOWER(dt.fungsi)'), 'akt penagihan')
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
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
            ->orderBy('p.nama', 'asc')
            ->orderBy('dt.bln_setor', 'asc');

        $data = $query->get();

        $filename = "Export_Detil_PKM_Penagihan_{$tahun}_{$bulan}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8 agar angka/NPWP tidak corrupt di Excel
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Kolom CSV Detil Penagihan
            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'NIP JSPN', 'NAMA JSPN', 'FLAG SKP',
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR'
            ]);

            // Data Transaksi
            foreach ($data as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    "'{$row->npwp15}", // Menambahkan petik (') agar NPWP tidak terformat ilmiah (E+) di Excel
                    $row->nama_wp,
                    $row->nip_jspn,
                    $row->nama_jspn,
                    $row->flag_skp,
                    $row->kd_map,
                    $row->kd_bayar,
                    $row->fungsi,
                    $row->bln_setor,
                    $row->thn_setor,
                    $row->jml_setor
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}