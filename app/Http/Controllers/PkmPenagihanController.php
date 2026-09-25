<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPenagihanController extends Controller
{
    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $dspcFilter = $this->resolveDspcFilter($request);

        $sortInput = (string) $request->input('sort', 'nip_jspn');
        $sortDirectionInput = (string) $request->input('direction', 'asc');

        $allowedSorts = [
            'nip_jspn' => 'nip_jspn',
            'nama_jspn' => 'nama_jspn',
            'flag_skp' => 'flag_skp',
            'akt_penagihan' => 'akt_penagihan',
        ];

        $sortColumn = array_key_exists($sortInput, $allowedSorts) ? $sortInput : 'nip_jspn';
        $sortDirection = strtolower($sortDirectionInput) === 'desc' ? 'desc' : 'asc';

        $cacheKey = "pkm_penagihan_{$tahun}_{$bulan}_{$dspcFilter}_{$sortColumn}_{$sortDirection}";

        try {
            $pkmData = Cache::remember($cacheKey, 600, function () use ($bulan, $tahun, $dspcFilter, $sortColumn, $sortDirection) {
                $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

                return DB::table('detil_transaksi_wp as dt')
                    ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
                    ->leftJoinSub($subPegawai, 'p', function ($join) {
                        $join->on('mw.nip_js', '=', 'p.nip');
                    })
                    ->select([
                        DB::raw("COALESCE(mw.nip_js, 'Unassign') as nip_jspn"),
                        DB::raw("COALESCE(p.nama, 'Unassign') as nama_jspn"),
                        DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC') as flag_skp"),
                        DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) = 'akt penagihan' THEN dt.jml_setor ELSE 0 END) as akt_penagihan"),
                    ])
                    ->whereRaw('LOWER(dt.fungsi) = ?', ['akt penagihan'])
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [1, $bulan])
                    ->when($dspcFilter !== '', function ($query) use ($dspcFilter) {
                        if ($dspcFilter === 'DSPC') {
                            return $query->whereRaw('UPPER(TRIM(dt.flag_skp)) = ?', ['DSPC']);
                        }

                        return $query->where(function ($q) {
                            $q->whereRaw('UPPER(TRIM(dt.flag_skp)) != ?', ['DSPC'])
                                ->orWhereNull('dt.flag_skp');
                        });
                    })
                    ->groupBy(
                        DB::raw("COALESCE(mw.nip_js, 'Unassign')"),
                        DB::raw("COALESCE(p.nama, 'Unassign')"),
                        DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC')")
                    )
                    ->orderBy($sortColumn, $sortDirection)
                    ->get();
            });
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Penagihan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Penagihan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('penerimaan.pkmpenagihan', compact(
            'pkmData',
            'sortColumn',
            'sortDirection',
            'tahun',
            'bulan',
            'dspcFilter'
        ));
    }

    /**
     * Handle Export CSV Detil Transaksi Penagihan (Streaming & Hemat Memory)
     */
    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $dspcFilter = $this->resolveDspcFilter($request);

        $filename = "Export_Detil_PKM_Penagihan_{$tahun}_{$bulan}.csv";

        return response()->stream(function () use ($tahun, $bulan, $dspcFilter) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'NIP JSPN', 'NAMA JSPN', 'FLAG SKP',
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            try {
                $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

                $query = DB::table('detil_transaksi_wp as dt')
                    ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
                    ->leftJoinSub($subPegawai, 'p', function ($join) {
                        $join->on('mw.nip_js', '=', 'p.nip');
                    })
                    ->select([
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
                        'dt.fungsi',
                    ])
                    ->whereRaw('LOWER(dt.fungsi) = ?', ['akt penagihan'])
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [1, $bulan])
                    ->when($dspcFilter !== '', function ($query) use ($dspcFilter) {
                        if ($dspcFilter === 'DSPC') {
                            return $query->whereRaw('UPPER(TRIM(dt.flag_skp)) = ?', ['DSPC']);
                        }

                        return $query->where(function ($q) {
                            $q->whereRaw('UPPER(TRIM(dt.flag_skp)) != ?', ['DSPC'])
                                ->orWhereNull('dt.flag_skp');
                        });
                    })
                    ->orderBy('p.nama', 'asc')
                    ->orderBy('dt.bln_setor', 'asc');

                $index = 1;
                foreach ($query->cursor() as $row) {
                    fputcsv($file, [
                        $index++,
                        isset($row->npwp15) ? "{$row->npwp15}" : '',
                        $row->nama_wp,
                        $row->nip_jspn,
                        $row->nama_jspn,
                        $row->flag_skp,
                        $row->kd_map,
                        $row->kd_bayar,
                        $row->fungsi,
                        $row->bln_setor,
                        $row->thn_setor,
                        $row->jml_setor,
                    ]);

                    if ($index % 5000 === 0) {
                        $this->flushOutputBuffer();
                    }
                }
            } catch (QueryException $e) {
                Log::error('Gagal mengekspor detil PKM Penagihan.', [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'message' => $e->getMessage(),
                ]);

                fputcsv($file, ['ERROR', 'Gagal mengambil data dari database']);
            }

            fclose($file);
        }, 200, $this->csvDownloadHeaders($filename));
    }

    private function resolveDspcFilter(Request $request): string
    {
        $filter = (string) $request->input('dspc_filter', '');

        return in_array($filter, ['DSPC', 'NON-DSPC'], true) ? $filter : '';
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
            'Content-Type' => 'text/csv; charset=UTF-8',
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
