<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class PkmPemeriksaanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $search = trim($request->input('search', ''));

        // Parameter Sorting
        $sortColumn = $request->input('sort', 'total_akt_pemeriksaan');
        $sortDirection = $request->input('direction', 'desc');

        // Mapping nama kolom yang diizinkan untuk di-sort
        $allowedSorts = [
            'npwp'                  => 'dt.npwp15',
            'nama_wp'               => DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar')"),
            'kd_klu'                => DB::raw("COALESCE(mw.klu, '-')"),
            'nm_klu'                => DB::raw("COALESCE(k.nm_klu, '-')"),
            'total_akt_pemeriksaan' => DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt pemeriksaan' THEN dt.jml_setor ELSE 0 END)"),
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt pemeriksaan' THEN dt.jml_setor ELSE 0 END)");
        $sortDir = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $page = $request->input('page', 1);

        // Buat Cache Key unik berdasarkan parameter filter dan paginasi
        $cacheKey = "pkm_pemeriksaan_{$tahun}_{$bulan}_s" . md5($search) . "_{$sortColumn}_{$sortDirection}_p{$page}";

        // Simpan hasil query di Cache selama 10 Menit (600 detik)
        $pkmData = Cache::remember($cacheKey, 600, function () use ($bulan, $tahun, $search, $sortBy, $sortDir) {
            return DB::table('detil_transaksi_wp as dt')
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
                ->where('dt.thn_setor', $tahun)
                ->whereBetween('dt.bln_setor', [1, $bulan])
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
                ->paginate(10)
                ->withQueryString();
        });

        return view('penerimaan.pkmpemeriksaan', compact('pkmData', 'sortColumn', 'sortDirection'));
    }

    /**
     * Handle Export CSV/Excel Detil Transaksi Pemeriksaan
     */
    public function exportDetil(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $search = trim($request->input('search', ''));

        $query = DB::table('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
            ->select(
                'dt.npwp15',
                DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar') as nama_wp"),
                DB::raw("COALESCE(mw.klu, '-') as kd_klu"),
                DB::raw("COALESCE(k.nm_klu, '-') as nm_klu"),
                'dt.kd_map',
                'dt.kd_bayar',
                'dt.jml_setor',
                'dt.bln_setor',
                'dt.thn_setor',
                'dt.fungsi'
            )
            ->where(DB::raw('LOWER(dt.fungsi)'), 'akt pemeriksaan')
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->when($search, function ($query, $keyword) {
                return $query->where(function ($q) use ($keyword) {
                    $q->where('dt.npwp15', 'LIKE', "%{$keyword}%")
                      ->orWhere('mw.nama', 'LIKE', "%{$keyword}%")
                      ->orWhere('mw.klu', 'LIKE', "%{$keyword}%")
                      ->orWhere('k.nm_klu', 'LIKE', "%{$keyword}%");
                });
            })
            ->orderBy('mw.nama', 'asc')
            ->orderBy('dt.bln_setor', 'asc');

        $data = $query->get();

        $filename = "Export_Detil_PKM_Pemeriksaan_{$tahun}_{$bulan}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            
            // BOM UTF-8
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header Kolom CSV Detil Pemeriksaan
            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'KD KLU', 'NAMA KLU', 
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR'
            ]);

            // Isi Data Transaksi
            foreach ($data as $index => $row) {
                fputcsv($file, [
                    $index + 1,
                    "'{$row->npwp15}", // Menambahkan petik (') agar NPWP tidak terformat ilmiah (E+) di Excel
                    $row->nama_wp,
                    $row->kd_klu,
                    $row->nm_klu,
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