<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PkmPengawasanController extends Controller
{
    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $seksiFilter = trim((string) $request->input('seksi', ''));
        $sortColumn = (string) $request->input('sort', 'nama_seksi');
        $sortDirection = (string) $request->input('direction', 'asc');

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
        $sortColumn = array_key_exists($sortColumn, $allowedSorts) ? $sortColumn : 'nama_seksi';
        $sortDirection = $sortDir;

        $cacheKey = "pkm_pengawasan_{$tahun}_{$bulan}_" . md5($seksiFilter) . "_{$sortColumn}_{$sortDir}";

        try {
            $pkmData = Cache::remember($cacheKey, 600, function () use ($bulan, $tahun, $seksiFilter, $sortColumn, $sortBy, $sortDir) {
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
                        DB::raw('SUM(dt.jml_setor) as total_pkm_pengawasan')
                    )
                    ->whereIn('dt.fungsi', [
                        'Akt Pengawasan',
                        'Lainnya',
                        'WRA Pengawasan',
                        'akt pengawasan',
                        'lainnya',
                        'wra pengawasan',
                    ])
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [1, $bulan])
                    ->when($seksiFilter !== '', function ($q) use ($seksiFilter) {
                        if ($seksiFilter === 'Unassign') {
                            return $q->whereNull('s.nama');
                        }

                        return $q->where('s.nama', $seksiFilter);
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
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Pengawasan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Pengawasan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('penerimaan.pkmpengawasan', compact('pkmData', 'daftarSeksi', 'sortColumn', 'sortDirection'));
    }

    /**
     * Handle Export CSV Detil Transaksi Pengawasan (Streaming & Hemat Memory)
     */
    public function exportDetil(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $seksiFilter = trim((string) $request->input('seksi', ''));

        $filename = "Export_Detil_PKM_Pengawasan_{$tahun}_{$bulan}.csv";

        return response()->stream(function () use ($tahun, $bulan, $seksiFilter) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'SEKSI', 'NAMA AR',
                'FUNGSI', 'KD MAP', 'KD BAYAR', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            try {
                $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

                $query = DB::table('detil_transaksi_wp as dt')
                    ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
                    ->leftJoinSub($subPegawai, 'p', function ($join) {
                        $join->on('mw.nip_ar', '=', 'p.nip');
                    })
                    ->leftJoin('seksi as s', 'p.seksi', '=', 's.id')
                    ->select(
                        'dt.npwp15',
                        DB::raw("COALESCE(mw.nama, '-') as nama_wp"),
                        'dt.kd_map',
                        'dt.kd_bayar',
                        'dt.jml_setor',
                        'dt.thn_setor',
                        'dt.bln_setor',
                        'dt.fungsi',
                        DB::raw("COALESCE(mw.nip_ar, 'Unassign') as nip_ar"),
                        DB::raw("COALESCE(p.nama, 'Unassign') as nama_ar"),
                        DB::raw("COALESCE(s.nama, 'Unassign') as nama_seksi")
                    )
                    ->whereIn('dt.fungsi', [
                        'Akt Pengawasan',
                        'Lainnya',
                        'WRA Pengawasan',
                        'akt pengawasan',
                        'lainnya',
                        'wra pengawasan',
                    ])
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [1, $bulan])
                    ->when($seksiFilter !== '', function ($q) use ($seksiFilter) {
                        if ($seksiFilter === 'Unassign') {
                            return $q->whereNull('s.nama');
                        }

                        return $q->where('s.nama', $seksiFilter);
                    })
                    ->orderBy('s.nama', 'asc')
                    ->orderBy('p.nama', 'asc');

                $index = 1;
                foreach ($query->cursor() as $row) {
                    fputcsv($file, [
                        $index++,
                        isset($row->npwp15) ? "'{$row->npwp15}" : '',
                        $row->nama_wp,
                        $row->nama_seksi,
                        $row->nama_ar,
                        $row->fungsi,
                        $row->kd_map,
                        $row->kd_bayar,
                        $row->bln_setor,
                        $row->thn_setor,
                        $row->jml_setor,
                    ]);

                    if ($index % 1000 === 0) {
                        $this->flushOutputBuffer();
                    }
                }
            } catch (QueryException $e) {
                Log::error('Gagal mengekspor detil PKM Pengawasan.', [
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
