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
                        SUM(CASE WHEN thn_setor = {$thnIni} THEN total_setor ELSE 0 END) as penerimaanSaatIni,
                        SUM(CASE WHEN thn_setor = {$thnIni} AND bln_setor < {$blnAkhir} THEN total_setor ELSE 0 END) as penerimaanBlnLalu,
                        SUM(CASE WHEN thn_setor = {$thnLalu} THEN total_setor ELSE 0 END) as penerimaanThnLalu,
                        SUM(CASE WHEN thn_setor = {$thnIni} AND jenis = 'PPM' THEN total_setor ELSE 0 END) as realisasiPPM,
                        SUM(CASE WHEN thn_setor = {$thnIni} AND jenis IN ('PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA') THEN total_setor ELSE 0 END) as realisasiPKM,
                        SUM(CASE WHEN thn_setor = {$thnIni} AND jenis = 'PBP' THEN total_setor ELSE 0 END) as realisasiPBP,
                        SUM(CASE WHEN thn_setor = {$thnIni} AND fungsi IN ('akt pengawasan', 'lainnya', 'wra pengawasan') THEN total_setor ELSE 0 END) as realisasiPengawasan,
                        SUM(CASE WHEN thn_setor = {$thnIni} AND fungsi = 'akt pemeriksaan' THEN total_setor ELSE 0 END) as realisasiPemeriksaan,
                        SUM(CASE WHEN thn_setor = {$thnIni} AND fungsi = 'akt penagihan' THEN total_setor ELSE 0 END) as realisasiPenagihan,
                        SUM(CASE WHEN thn_setor = {$thnLalu} AND jenis = 'PPM' THEN total_setor ELSE 0 END) as realisasiPPMLalu,
                        SUM(CASE WHEN thn_setor = {$thnLalu} AND jenis IN ('PKM', 'PKM AKTIVITAS', 'PKM LAINNYA', 'PKM WRA') THEN total_setor ELSE 0 END) as realisasiPKMLalu,
                        SUM(CASE WHEN thn_setor = {$thnLalu} AND fungsi IN ('akt pengawasan', 'lainnya', 'wra pengawasan') THEN total_setor ELSE 0 END) as realisasiPengawasanLalu,
                        SUM(CASE WHEN thn_setor = {$thnLalu} AND fungsi = 'akt pemeriksaan' THEN total_setor ELSE 0 END) as realisasiPemeriksaanLalu,
                        SUM(CASE WHEN thn_setor = {$thnLalu} AND fungsi = 'akt penagihan' THEN total_setor ELSE 0 END) as realisasiPenagihanLalu
                    ")
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
            'blnAwal',
            'blnAkhir',
            'target',
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
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            try {
                $query = DB::table('detil_transaksi_wp as dt')
                    ->where('dt.thn_setor', $tahun)
                    ->whereBetween('dt.bln_setor', [$bulanAwal, $bulanAkhir])
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
        $tahun = (int) $request->input('tahun', date('Y'));
        
        // Membaca bulan awal dan bulan akhir (support backward compatibility untuk param 'bulan')
        $bulanAwal = (int) $request->input('bulan_awal', 1);
        $bulanAkhir = (int) $request->input('bulan_akhir', $request->input('bulan', date('n')));

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        if ($bulanAwal < 1 || $bulanAwal > 12) {
            $bulanAwal = 1;
        }

        if ($bulanAkhir < 1 || $bulanAkhir > 12) {
            $bulanAkhir = (int) date('n');
        }

        // Jika bulan awal diset lebih besar dari bulan akhir, samakan nilai bulan awal dengan bulan akhir
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