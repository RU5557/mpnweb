<?php

namespace App\Repositories;

use App\Models\DetilTransaksiWp;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPengawasanRepository
{
    /**
     * Ambil daftar nama Seksi Pengawasan (Cached 24 Jam)
     */
    public function getDaftarSeksi(): array
    {
        return Cache::remember('daftar_seksi_pengawasan_v2', 86400, function () {
            $seksi = DB::table('seksi')
                ->where('nama', 'LIKE', '%Pengawasan%')
                ->orderBy('nama', 'asc')
                ->pluck('nama')
                ->toArray();

            $seksi[] = 'Unassign';

            return $seksi;
        });
    }

    /**
     * Ambil Data Agregasi Ringkasan PKM Pengawasan per AR
     */
    public function getSummaryPkm(int $tahun, int $bulan, string $seksiFilter, string $sortColumn, string $sortDirection): Collection
    {
        $allowedSorts = [
            'nama_seksi' => "COALESCE(s.nama, 'Unassign')",
            'nama_ar' => "COALESCE(p.nama, 'Unassign')",
            'total_akt_pengawasan' => 'total_akt_pengawasan',
            'total_lainnya' => 'total_lainnya',
            'total_wra_pengawasan' => 'total_wra_pengawasan',
            'total_pkm_pengawasan' => 'total_pkm_pengawasan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? "COALESCE(s.nama, 'Unassign')";
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $cacheKey = "pkm_pengawasan_v2_{$tahun}_{$bulan}_".md5($seksiFilter)."_{$sortColumn}_{$sortDir}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $seksiFilter, $sortColumn, $sortBy, $sortDir) {
            $query = $this->buildBaseQuery($tahun, $bulan, $seksiFilter);

            return $query
                ->select([
                    DB::raw("COALESCE(mw.nip_ar, 'Unassign') as nip_ar"),
                    DB::raw("COALESCE(p.nama, 'Unassign') as nama_ar"),
                    DB::raw("COALESCE(s.nama, 'Unassign') as nama_seksi"),
                    DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) LIKE 'akt%' THEN dt.jml_setor ELSE 0 END) as total_akt_pengawasan"),
                    DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) LIKE 'lain%' THEN dt.jml_setor ELSE 0 END) as total_lainnya"),
                    DB::raw("SUM(CASE WHEN LOWER(dt.fungsi) LIKE 'wra%' THEN dt.jml_setor ELSE 0 END) as total_wra_pengawasan"),
                    DB::raw('SUM(dt.jml_setor) as total_pkm_pengawasan'),
                ])
                ->groupBy(
                    DB::raw("COALESCE(mw.nip_ar, 'Unassign')"),
                    DB::raw("COALESCE(p.nama, 'Unassign')"),
                    DB::raw("COALESCE(s.nama, 'Unassign')")
                )
                ->orderBy(DB::raw($sortBy), $sortDir)
                ->when($sortColumn === 'nama_seksi', function ($q) {
                    return $q->orderBy(DB::raw("COALESCE(p.nama, 'Unassign')"), 'asc');
                })
                ->get();
        });
    }

    /**
     * Export Detil PKM Pengawasan ke Streamed CSV
     */
    public function exportDetilCsv(int $tahun, int $bulan, string $seksiFilter): StreamedResponse
    {
        $filename = "Export_Detil_PKM_Pengawasan_{$tahun}_{$bulan}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tahun, $bulan, $seksiFilter) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Excel

            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'SEKSI', 'NAMA AR',
                'FUNGSI', 'KD MAP', 'KD BAYAR', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            $query = $this->buildBaseQuery($tahun, $bulan, $seksiFilter)
                ->select([
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
                    DB::raw("COALESCE(s.nama, 'Unassign') as nama_seksi"),
                ])
                ->orderBy('s.nama', 'asc')
                ->orderBy('p.nama', 'asc');

            $index = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $index++,
                    ! empty($row->npwp15) ? $row->npwp15 : '',
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

                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Base Query Builder tanpa Hydration untuk Performa Maksimal
     */
    private function buildBaseQuery(int $tahun, int $bulan, string $seksiFilter)
    {
        $tahunSaatIni = (int) date('Y');

        return DetilTransaksiWp::query()
            ->toBase()
            ->from('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('pegawai as p', function ($join) use ($tahunSaatIni) {
                $join->on('mw.nip_ar', '=', 'p.nip')
                    ->where('p.tahun', '=', $tahunSaatIni);
            })
            ->leftJoin('seksi as s', 'p.seksi', '=', 's.id')
            ->whereIn('dt.fungsi', [
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
            });
    }
}
