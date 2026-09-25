<?php

namespace App\Http\Controllers;

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
        [$thnIni, $blnAwal, $blnAkhir] = $this->resolvePeriod($request);
        $thnLalu = $thnIni - 1;

        $cacheKey = "dashboard_summary_{$thnIni}_{$blnAwal}_{$blnAkhir}";

        try {
            $target = Cache::remember("dashboard_target_{$thnIni}", 600, function () use ($thnIni) {
                return Target::where('tahun', $thnIni)->first();
            });

            $penerimaanData = Cache::remember($cacheKey, 600, function () use ($thnIni, $thnLalu, $blnAwal, $blnAkhir) {
                return DB::table('summary_mart_penerimaan')
                    ->whereIn('thn_setor', [$thnIni, $thnLalu])
                    ->whereBetween('bln_setor', [$blnAwal, $blnAkhir])
                    ->selectRaw("
                        SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as penerimaanSaatIni,
                        SUM(CASE WHEN thn_setor = ? AND bln_setor < ? THEN total_setor ELSE 0 END) as penerimaanBlnLalu,
                        SUM(CASE WHEN thn_setor = ? THEN total_setor ELSE 0 END) as penerimaanThnLalu,
                        SUM(CASE WHEN thn_setor = ? AND jenis = 'PPM' THEN total_setor ELSE 0 END) as realisasiPPM,
                        SUM(CASE WHEN thn_setor = ? AND jenis IN ('PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA') THEN total_setor ELSE 0 END) as realisasiPKM,
                        SUM(CASE WHEN thn_setor = ? AND jenis = 'PBP' THEN total_setor ELSE 0 END) as realisasiPBP,
                        SUM(CASE WHEN thn_setor = ? AND fungsi IN ('akt pengawasan', 'lainnya', 'wra pengawasan') THEN total_setor ELSE 0 END) as realisasiPengawasan,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = 'akt pemeriksaan' THEN total_setor ELSE 0 END) as realisasiPemeriksaan,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = 'akt penagihan' THEN total_setor ELSE 0 END) as realisasiPenagihan,
                        SUM(CASE WHEN thn_setor = ? AND jenis = 'PPM' THEN total_setor ELSE 0 END) as realisasiPPMLalu,
                        SUM(CASE WHEN thn_setor = ? AND jenis IN ('PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA') THEN total_setor ELSE 0 END) as realisasiPKMLalu,
                        SUM(CASE WHEN thn_setor = ? AND fungsi IN ('akt pengawasan', 'lainnya', 'wra pengawasan') THEN total_setor ELSE 0 END) as realisasiPengawasanLalu,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = 'akt pemeriksaan' THEN total_setor ELSE 0 END) as realisasiPemeriksaanLalu,
                        SUM(CASE WHEN thn_setor = ? AND fungsi = 'akt penagihan' THEN total_setor ELSE 0 END) as realisasiPenagihanLalu
                    ", [
                        $thnIni, $thnIni, $blnAkhir, $thnLalu,
                        $thnIni, $thnIni, $thnIni,
                        $thnIni, $thnIni, $thnIni,
                        $thnLalu, $thnLalu, $thnLalu,
                        $thnLalu, $thnLalu,
                    ])
                    ->first();
            });
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary dashboard.', [
                'tahun' => $thnIni,
                'bulan_awal' => $blnAwal,
                'bulan_akhir' => $blnAkhir,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data dashboard sedang tidak tersedia. Silakan coba lagi.');
        }

        // --- Extrak Nilai Nominal ---
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

        // --- Kalkulasi Indikator & Growth ---
        $targetKantor = $target?->target_kantor ?? 0;
        $capaianKantor = $targetKantor > 0 ? ($penerimaanSaatIni / $targetKantor) * 100 : 0;

        $growthMoM = $penerimaanBlnLalu > 0 ? (($penerimaanSaatIni - $penerimaanBlnLalu) / $penerimaanBlnLalu) * 100 : 0;
        $growthYoY = $penerimaanThnLalu > 0 ? (($penerimaanSaatIni - $penerimaanThnLalu) / $penerimaanThnLalu) * 100 : 0;

        // Metrics Card Configs
        $metrics = [
            'ppm' => [
                'target' => $target?->target_ppm ?? 0,
                'realisasi' => $realisasiPPM,
                'persen' => ($target?->target_ppm ?? 0) > 0 ? ($realisasiPPM / $target->target_ppm) * 100 : 0,
                'growthYoY' => $realisasiPPMLalu > 0 ? (($realisasiPPM - $realisasiPPMLalu) / $realisasiPPMLalu) * 100 : 0,
            ],
            'pkm' => [
                'target' => $target?->target_pkm ?? 0,
                'realisasi' => $realisasiPKM,
                'persen' => ($target?->target_pkm ?? 0) > 0 ? ($realisasiPKM / $target->target_pkm) * 100 : 0,
                'growthYoY' => $realisasiPKMLalu > 0 ? (($realisasiPKM - $realisasiPKMLalu) / $realisasiPKMLalu) * 100 : 0,
            ],
            'pbp' => [
                'target' => $target?->target_pbp ?? 0,
                'realisasi' => $realisasiPBP,
                'persen' => ($target?->target_pbp ?? 0) > 0 ? ($realisasiPBP / $target->target_pbp) * 100 : 0,
                'sisa' => max(0, ($target?->target_pbp ?? 0) - $realisasiPBP),
            ],
            'pengawasan' => [
                'target' => $target?->target_pkm_pengawasan ?? 0,
                'realisasi' => $realisasiPengawasan,
                'persen' => ($target?->target_pkm_pengawasan ?? 0) > 0 ? ($realisasiPengawasan / $target->target_pkm_pengawasan) * 100 : 0,
                'growthYoY' => $realisasiPengawasanLalu > 0 ? (($realisasiPengawasan - $realisasiPengawasanLalu) / $realisasiPengawasanLalu) * 100 : 0,
            ],
            'pemeriksaan' => [
                'target' => $target?->target_pkm_pemeriksaan ?? 0,
                'realisasi' => $realisasiPemeriksaan,
                'persen' => ($target?->target_pkm_pemeriksaan ?? 0) > 0 ? ($realisasiPemeriksaan / $target->target_pkm_pemeriksaan) * 100 : 0,
                'growthYoY' => $realisasiPemeriksaanLalu > 0 ? (($realisasiPemeriksaan - $realisasiPemeriksaanLalu) / $realisasiPemeriksaanLalu) * 100 : 0,
            ],
            'penagihan' => [
                'target' => $target?->target_pkm_penagihan ?? 0,
                'realisasi' => $realisasiPenagihan,
                'persen' => ($target?->target_pkm_penagihan ?? 0) > 0 ? ($realisasiPenagihan / $target->target_pkm_penagihan) * 100 : 0,
                'growthYoY' => $realisasiPenagihanLalu > 0 ? (($realisasiPenagihan - $realisasiPenagihanLalu) / $realisasiPenagihanLalu) * 100 : 0,
            ],
        ];

        // List Nama Bulan untuk Filter Dropdown
        $listBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        return view('penerimaan.dashboard', compact(
            'thnIni',
            'blnAwal',
            'blnAkhir',
            'listBulan',
            'capaianKantor',
            'penerimaanSaatIni',
            'penerimaanBlnLalu',
            'penerimaanThnLalu',
            'growthMoM',
            'growthYoY',
            'metrics'
        ));
    }

    public function exportDetil(Request $request)
    {
        [$tahun, $bulanAwal, $bulanAkhir] = $this->resolvePeriod($request);

        $filename = "Export_Detil_Transaksi_Dashboard_{$tahun}_{$bulanAwal}_sd_{$bulanAkhir}.csv";

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tahun, $bulanAwal, $bulanAkhir) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF));

            try {
                $query = DB::table('detil_transaksi_wp as dt')
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [$bulanAwal, $bulanAkhir])
                    ->orderBy('dt.bln_setor', 'asc');

                $isHeaderWritten = false;
                $rowCount = 0;

                foreach ($query->cursor() as $row) {
                    $rowArray = (array) $row;

                    if (! $isHeaderWritten) {
                        fputcsv($file, array_keys($rowArray));
                        $isHeaderWritten = true;
                    }

                    fputcsv($file, $rowArray);
                    $rowCount++;

                    if ($rowCount % 1000 === 0) {
                        $this->flushOutputBuffer();
                    }
                }

                if (! $isHeaderWritten) {
                    fputcsv($file, ['INFO']);
                    fputcsv($file, ['Tidak ada data transaksi']);
                }
            } catch (QueryException $e) {
                Log::error('Gagal mengekspor detil transaksi dashboard.', [
                    'tahun' => $tahun,
                    'bulan_awal' => $bulanAwal,
                    'bulan_akhir' => $bulanAkhir,
                    'message' => $e->getMessage(),
                ]);

                fputcsv($file, ['ERROR']);
                fputcsv($file, ['Gagal mengambil data dari database']);
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private function resolvePeriod(Request $request): array
    {
        $currentYear = (int) date('Y');
        $tahun = (int) $request->input('tahun', $currentYear);

        $bulanAwal = (int) $request->input('bulan_awal', 1);
        $bulanAkhir = (int) $request->input('bulan_akhir', $request->input('bulan', date('n')));

        if ($tahun < 2000 || $tahun > $currentYear + 1) {
            $tahun = $currentYear;
        }

        if ($bulanAwal < 1 || $bulanAwal > 12) {
            $bulanAwal = 1;
        }

        if ($bulanAkhir < 1 || $bulanAkhir > 12) {
            $bulanAkhir = (int) date('n');
        }

        if ($bulanAwal > $bulanAkhir) {
            $bulanAwal = $bulanAkhir;
        }

        return [$tahun, $bulanAwal, $bulanAkhir];
    }

    private function flushOutputBuffer(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }
}
