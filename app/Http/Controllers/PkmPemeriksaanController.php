<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PkmPemeriksaanController extends Controller
{
    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $search = trim((string) $request->input('search', ''));
        $sortColumn = (string) $request->input('sort', 'total_akt_pemeriksaan');
        $sortDirection = (string) $request->input('direction', 'desc');
        $page = max(1, (int) $request->input('page', 1));

        $allowedSorts = [
            'npwp' => 'dt.npwp15',
            'nama_wp' => DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar')"),
            'kd_klu' => DB::raw("COALESCE(mw.klu, '-')"),
            'nm_klu' => DB::raw("COALESCE(k.nm_klu, '-')"),
            'total_akt_pemeriksaan' => DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt pemeriksaan' THEN dt.jml_setor ELSE 0 END)"),
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt pemeriksaan' THEN dt.jml_setor ELSE 0 END)");
        $sortDir = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';
        $sortColumn = array_key_exists($sortColumn, $allowedSorts) ? $sortColumn : 'total_akt_pemeriksaan';
        $sortDirection = $sortDir;

        $cacheKey = "pkm_pemeriksaan_{$tahun}_{$bulan}_s" . md5($search) . "_{$sortColumn}_{$sortDir}_p{$page}";

        try {
            $pkmData = Cache::remember($cacheKey, 600, function () use ($bulan, $tahun, $search, $sortBy, $sortDir) {
                $like = '%' . addcslashes($search, '%_\\') . '%';

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
                    ->whereRaw('LOWER(dt.fungsi) = ?', ['akt pemeriksaan'])
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [1, $bulan])
                    ->when($search !== '', function ($query) use ($like) {
                        return $query->where(function ($q) use ($like) {
                            $q->where('dt.npwp15', 'like', $like)
                                ->orWhere('mw.nama', 'like', $like)
                                ->orWhere('mw.klu', 'like', $like)
                                ->orWhere('k.nm_klu', 'like', $like);
                        });
                    })
                    ->groupBy('dt.npwp15', 'mw.nama', 'mw.klu', 'k.nm_klu')
                    ->orderBy($sortBy, $sortDir)
                    ->paginate(10)
                    ->withQueryString();
            });
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Pemeriksaan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Pemeriksaan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('penerimaan.pkmpemeriksaan', compact('pkmData', 'sortColumn', 'sortDirection'));
    }

    /**
     * Handle Export CSV Detil Transaksi Pemeriksaan (Streaming & Hemat Memory)
     */
    public function exportDetil(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $search = trim((string) $request->input('search', ''));
        $like = '%' . addcslashes($search, '%_\\') . '%';

        $filename = "Export_Detil_PKM_Pemeriksaan_{$tahun}_{$bulan}.csv";

        return response()->stream(function () use ($tahun, $bulan, $search, $like) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'KD KLU', 'NAMA KLU',
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            try {
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
                    ->whereRaw('LOWER(dt.fungsi) = ?', ['akt pemeriksaan'])
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [1, $bulan])
                    ->when($search !== '', function ($query) use ($like) {
                        return $query->where(function ($q) use ($like) {
                            $q->where('dt.npwp15', 'like', $like)
                                ->orWhere('mw.nama', 'like', $like)
                                ->orWhere('mw.klu', 'like', $like)
                                ->orWhere('k.nm_klu', 'like', $like);
                        });
                    })
                    ->orderBy('mw.nama', 'asc')
                    ->orderBy('dt.bln_setor', 'asc');

                $index = 1;
                foreach ($query->cursor() as $row) {
                    fputcsv($file, [
                        $index++,
                        isset($row->npwp15) ? "'{$row->npwp15}" : '',
                        $row->nama_wp,
                        $row->kd_klu,
                        $row->nm_klu,
                        $row->kd_map,
                        $row->kd_bayar,
                        $row->fungsi,
                        $row->bln_setor,
                        $row->thn_setor,
                        $row->jml_setor,
                    ]);

                    if ($index % 1000 === 0) {
                        $this->flushOutputBuffer();
                    }
                }
            } catch (QueryException $e) {
                Log::error('Gagal mengekspor detil PKM Pemeriksaan.', [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'message' => $e->getMessage(),
                ]);

                fputcsv($file, ['ERROR', 'Gagal mengambil data dari database']);
            }

            fclose($file);
        }, 200, $this->csvDownloadHeaders($filename));
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function resolvePeriod(Request $request): array
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('n'));

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }

        return [$tahun, $bulan];
    }

    /**
     * @return array<string, string>
     */
    private function csvDownloadHeaders(string $filename): array
    {
        return [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];
    }

    private function flushOutputBuffer(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
