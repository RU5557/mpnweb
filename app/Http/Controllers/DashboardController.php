<?php

namespace App\Http\Controllers;

use App\Models\RollingText;
use App\Models\Target;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [$thnIni, $blnIni] = $this->resolvePeriod($request);
        $thnLalu = $thnIni - 1;

        $cacheKey = "dashboard_summary_{$thnIni}_{$blnIni}";

        try {
            $target = Cache::remember("dashboard_target_{$thnIni}", 600, function () use ($thnIni) {
                return Target::where('tahun', $thnIni)->first();
            });

            // $rollingText = Cache::remember('dashboard_rolling_text', 600, function () {
            //     return RollingText::latest('tanggal')->first();
            // });

            $penerimaanData = Cache::remember($cacheKey, 600, function () use ($thnIni, $thnLalu, $blnIni) {
                return DB::table('summary_mart_penerimaan')
                    ->whereIn('thn_setor', [$thnIni, $thnLalu])
                    ->where('bln_setor', '<=', $blnIni)
                    ->selectRaw('
                        SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as penerimaanSaatIni,
                        SUM(CASE WHEN thn_setor = ? AND bln_setor < ? THEN total_setor ELSE 0 END) as penerimaanBlnLalu,
                        SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as penerimaanThnLalu,
                        SUM(CASE WHEN thn_setor = ? AND jenis = \'PPM\' THEN total_setor ELSE 0 END) as realisasiPPM,
                        SUM(CASE WHEN thn_setor = ? AND jenis IN (\'PKM\', \'PKM AKTIVITAS\', \'PKM LAINNYA\', \'PKM WRA\') THEN total_setor ELSE 0 END) as realisasiPKM,
                        SUM(CASE WHEN thn_setor = ? AND jenis = \'PBP\' THEN total_setor ELSE 0 END) as realisasiPBP,
                        SUM(CASE WHEN thn_setor = ? AND fungsi IN (\'akt pengawasan\', \'lainnya\', \'wra pengawasan\') THEN total_setor ELSE 0 END) as realisasiPengawasan,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = \'akt pemeriksaan\' THEN total_setor ELSE 0 END) as realisasiPemeriksaan,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = \'akt penagihan\' THEN total_setor ELSE 0 END) as realisasiPenagihan,
                        SUM(CASE WHEN thn_setor = ? AND jenis = \'PPM\' THEN total_setor ELSE 0 END) as realisasiPPMLalu,
                        SUM(CASE WHEN thn_setor = ? AND jenis IN (\'PKM\', \'PKM AKTIVITAS\', \'PKM LAINNYA\', \'PKM WRA\') THEN total_setor ELSE 0 END) as realisasiPKMLalu,
                        SUM(CASE WHEN thn_setor = ? AND fungsi IN (\'akt pengawasan\', \'lainnya\', \'wra pengawasan\') THEN total_setor ELSE 0 END) as realisasiPengawasanLalu,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = \'akt pemeriksaan\' THEN total_setor ELSE 0 END) as realisasiPemeriksaanLalu,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = \'akt penagihan\' THEN total_setor ELSE 0 END) as realisasiPenagihanLalu
                    ', [
                        $thnIni,
                        $thnIni,
                        $blnIni,
                        $thnLalu,
                        $thnIni,
                        $thnIni,
                        $thnIni,
                        $thnIni,
                        $thnIni,
                        $thnIni,
                        $thnLalu,
                        $thnLalu,
                        $thnLalu,
                        $thnLalu,
                        $thnLalu,
                    ])
                    ->first();
            });
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary dashboard.', [
                'tahun' => $thnIni,
                'bulan' => $blnIni,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data dashboard sedang tidak tersedia. Silakan coba lagi.');
        }

        $penerimaanSaatIni = $penerimaanData?->penerimaanSaatIni ?? 0;
        $penerimaanBlnLalu = $penerimaanData?->penerimaanBlnLalu ?? 0;
        $penerimaanThnLalu = $penerimaanData?->penerimaanThnLalu ?? 0;

        $realisasiPPM = $penerimaanData?->realisasiPPM ?? 0;
        $realisasiPKM = $penerimaanData?->realisasiPKM ?? 0;
        $realisasiPBP = $penerimaanData?->realisasiPBP ?? 0;

        $realisasiPengawasan = $penerimaanData?->realisasiPengawasan ?? 0;
        $realisasiPemeriksaan = $penerimaanData?->realisasiPemeriksaan ?? 0;
        $realisasiPenagihan = $penerimaanData?->realisasiPenagihan ?? 0;

        $realisasiPPMLalu = $penerimaanData?->realisasiPPMLalu ?? 0;
        $realisasiPKMLalu = $penerimaanData?->realisasiPKMLalu ?? 0;

        $realisasiPengawasanLalu = $penerimaanData?->realisasiPengawasanLalu ?? 0;
        $realisasiPemeriksaanLalu = $penerimaanData?->realisasiPemeriksaanLalu ?? 0;
        $realisasiPenagihanLalu = $penerimaanData?->realisasiPenagihanLalu ?? 0;

        $targetKantor = $target?->target_kantor ?? 0;
        $capaianKantor = $targetKantor > 0 ? ($penerimaanSaatIni / $targetKantor) * 100 : 0;

        return view('penerimaan.dashboard', compact(
            'thnIni',
            'blnIni',
            'target',
            // 'rollingText',
            'capaianKantor',
            'penerimaanSaatIni',
            'penerimaanBlnLalu',
            'penerimaanThnLalu',
            'realisasiPPM',
            'realisasiPKM',
            'realisasiPBP',
            'realisasiPengawasan',
            'realisasiPemeriksaan',
            'realisasiPenagihan',
            'realisasiPPMLalu',
            'realisasiPKMLalu',
            'realisasiPengawasanLalu',
            'realisasiPemeriksaanLalu',
            'realisasiPenagihanLalu'
        ));
    }

    /**
     * Handle Export CSV Detil Transaksi Dashboard (Streaming & Hemat Memory)
     */
    public function exportDetil(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);

        $filename = "Export_Detil_Transaksi_Dashboard_{$tahun}_{$bulan}.csv";

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

            try {
                $query = DB::table('detil_transaksi_wp as dt')
                    ->where('dt.thn_setor', $tahun)
                    ->where('dt.bln_setor', '<=', $bulan)
                    ->orderBy('dt.bln_setor', 'asc');

                $isHeaderWritten = false;
                $rowCount = 0;

                foreach ($query->cursor() as $row) {
                    $rowArray = (array) $row;

                    if (!$isHeaderWritten) {
                        fputcsv($file, array_keys($rowArray));
                        $isHeaderWritten = true;
                    }

                    fputcsv($file, $rowArray);
                    $rowCount++;

                    if ($rowCount % 1000 === 0) {
                        $this->flushOutputBuffer();
                    }
                }

                if (!$isHeaderWritten) {
                    fputcsv($file, ['INFO']);
                    fputcsv($file, ['Tidak ada data transaksi']);
                }
            } catch (QueryException $e) {
                Log::error('Gagal mengekspor detil transaksi dashboard.', [
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'message' => $e->getMessage(),
                ]);

                fputcsv($file, ['ERROR']);
                fputcsv($file, ['Gagal mengambil data dari database']);
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

    private function flushOutputBuffer(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
