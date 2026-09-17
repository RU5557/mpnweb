<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PpmController extends Controller
{
    public function index(Request $request)
    {
        [$thnIni, $blnIni] = $this->resolvePeriod($request);

        $cacheKey = "ppm_summary_{$thnIni}_{$blnIni}";

        try {
            $summary = Cache::remember($cacheKey, 600, function () use ($thnIni, $blnIni) {
                return [
                    'topWp' => $this->topPpmGrouped('nama_wp', $thnIni, $blnIni),
                    'topKategori' => $this->topPpmGrouped('nm_kategori', $thnIni, $blnIni),
                    'topJenisPajak' => $this->topPpmGrouped('jenis_pajak', $thnIni, $blnIni),
                    'topWpPpnDn' => $this->topPpmGrouped('nama_wp', $thnIni, $blnIni, '411211'),
                    'topWpPpnImpor' => $this->topPpmGrouped('nama_wp', $thnIni, $blnIni, '411212'),
                    'topWpPphBadan' => $this->topPpmGrouped('nama_wp', $thnIni, $blnIni, '411126'),
                    'topWpPph21' => $this->topPpmGrouped('nama_wp', $thnIni, $blnIni, '411122'),
                ];
            });
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PPM.', [
                'tahun' => $thnIni,
                'bulan' => $blnIni,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PPM sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('penerimaan.ppm', [
            'thnIni' => $thnIni,
            'blnIni' => $blnIni,
            'topWp' => $summary['topWp'],
            'topKategori' => $summary['topKategori'],
            'topJenisPajak' => $summary['topJenisPajak'],
            'topWpPpnDn' => $summary['topWpPpnDn'],
            'topWpPpnImpor' => $summary['topWpPpnImpor'],
            'topWpPphBadan' => $summary['topWpPphBadan'],
            'topWpPph21' => $summary['topWpPph21'],
        ]);
    }

    /**
     * Handle Export CSV Detil Transaksi PPM (Streaming & Hemat Memory)
     */
    public function exportDetil(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);

        $filename = "Export_Detil_PPM_{$tahun}_{$bulan}.csv";

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tahun, $bulan) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'KD KLU', 'SEKTOR',
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'JENIS', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            try {
                $query = DB::table('detil_transaksi_wp as dt')
                    ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
                    ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
                    ->select(
                        'dt.npwp15',
                        DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar') as nama_wp"),
                        DB::raw("COALESCE(mw.klu, '-') as kd_klu"),
                        DB::raw("COALESCE(k.nm_kategori, '-') as nm_kategori"),
                        'dt.kd_map',
                        'dt.kd_bayar',
                        'dt.fungsi',
                        'dt.jenis',
                        'dt.bln_setor',
                        'dt.thn_setor',
                        'dt.jml_setor'
                    )
                    ->where('dt.jenis', 'PPM')
                    ->where('dt.thn_setor', $tahun)
                    ->where('dt.bln_setor', '<=', $bulan)
                    ->orderBy('mw.nama', 'asc')
                    ->orderBy('dt.bln_setor', 'asc');

                $index = 1;
                foreach ($query->cursor() as $row) {
                    fputcsv($file, [
                        $index++,
                        $row->npwp15,
                        $row->nama_wp,
                        $row->kd_klu,
                        $row->nm_kategori,
                        $row->kd_map,
                        $row->kd_bayar,
                        $row->fungsi,
                        $row->jenis,
                        $row->bln_setor,
                        $row->thn_setor,
                        $row->jml_setor,
                    ]);

                    if ($index % 1000 === 0) {
                        $this->flushOutputBuffer();
                    }
                }
            } catch (QueryException $e) {
                Log::error('Gagal mengekspor detil transaksi PPM.', [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'message' => $e->getMessage(),
                ]);

                fputcsv($file, ['ERROR', 'Gagal mengambil data dari database']);
            }

            fclose($file);
        }, 200, $headers);
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

    private function topPpmGrouped(string $column, int $tahun, int $bulan, ?string $kdMap = null): Collection
    {
        $allowedColumns = ['nama_wp', 'nm_kategori', 'jenis_pajak'];

        if (!in_array($column, $allowedColumns, true)) {
            throw new \InvalidArgumentException('Kolom grouping PPM tidak valid.');
        }

        return DB::table('summary_mart_ppm')
            ->select($column, DB::raw('SUM(jml_setor) as total'))
            ->where('thn_setor', $tahun)
            ->where('bln_setor', '<=', $bulan)
            ->where('jenis', 'PPM')
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->when($kdMap !== null, function ($query) use ($kdMap) {
                return $query->where('kd_map', $kdMap);
            })
            ->groupBy($column)
            ->orderByDesc('total')
            ->limit(10)
            ->get();
    }

    private function flushOutputBuffer(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
