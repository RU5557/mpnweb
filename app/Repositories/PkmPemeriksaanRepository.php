<?php

namespace App\Repositories;

use App\Models\DetilTransaksiWp;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPemeriksaanRepository
{
    /**
     * Ambil Data Agregasi Ringkasan PKM Pemeriksaan per WP (Paginated + Cached)
     */
    public function getPaginatedPkm(
        int $tahun,
        int $bulan,
        string $search,
        string $sortColumn,
        string $sortDirection,
        int $page = 1,
        int $perPage = 10
    ): LengthAwarePaginator {
        $allowedSorts = [
            'npwp' => 'dt.npwp15',
            'nama_wp' => DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar')"),
            'kd_klu' => DB::raw("COALESCE(mw.klu, '-')"),
            'nm_klu' => DB::raw("COALESCE(k.nm_klu, '-')"),
            'total_akt_pemeriksaan' => 'total_akt_pemeriksaan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? 'total_akt_pemeriksaan';
        $sortDir = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $cacheKey = "pkm_pemeriksaan_v2_{$tahun}_{$bulan}_s".md5($search)."_{$sortColumn}_{$sortDir}_p{$page}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $search, $sortBy, $sortDir, $perPage) {
            return $this->buildBaseQuery($tahun, $bulan, $search)
                ->select([
                    'dt.npwp15',
                    DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar') as nama_wp"),
                    DB::raw("COALESCE(mw.klu, '-') as kd_klu"),
                    DB::raw("COALESCE(k.nm_klu, '-') as nm_klu"),
                    DB::raw('SUM(dt.jml_setor) as total_akt_pemeriksaan'),
                ])
                ->groupBy('dt.npwp15', 'mw.nama', 'mw.klu', 'k.nm_klu')
                ->orderBy($sortBy, $sortDir)
                ->paginate($perPage)
                ->withQueryString();
        });
    }

    /**
     * Export Detil Transaksi PKM Pemeriksaan ke Streamed CSV
     */
    public function exportDetilCsv(int $tahun, int $bulan, string $search): StreamedResponse
    {
        $filename = "Export_Detil_PKM_Pemeriksaan_{$tahun}_{$bulan}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($tahun, $bulan, $search) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM untuk MS Excel

            fputcsv($file, [
                'NO', 'NPWP', 'NAMA WP', 'KD KLU', 'NAMA KLU',
                'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR',
            ]);

            $query = $this->buildBaseQuery($tahun, $bulan, $search)
                ->select([
                    'dt.npwp15',
                    DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar') as nama_wp"),
                    DB::raw("COALESCE(mw.klu, '-') as kd_klu"),
                    DB::raw("COALESCE(k.nm_klu, '-') as nm_klu"),
                    'dt.kd_map',
                    'dt.kd_bayar',
                    'dt.jml_setor',
                    'dt.bln_setor',
                    'dt.thn_setor',
                    'dt.fungsi',
                ])
                ->orderBy('mw.nama', 'asc')
                ->orderBy('dt.bln_setor', 'asc');

            $index = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $index++,
                    ! empty($row->npwp15) ? $row->npwp15 : '',
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

                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }

            fclose($file);
        }, 200, $headers);
    }

    /**
     * Base Query Builder Tanpa Hydration (Menggunakan Index Komposit)
     */
    private function buildBaseQuery(int $tahun, int $bulan, string $search)
    {
        return DetilTransaksiWp::query()
            ->toBase()
            ->from('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('klu as k', 'mw.klu', '=', 'k.kd_klu')
            ->where('dt.fungsi', 'akt pemeriksaan')
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';

                return $query->where(function ($q) use ($like) {
                    $q->where('dt.npwp15', 'like', $like)
                        ->orWhere('mw.nama', 'like', $like)
                        ->orWhere('mw.klu', 'like', $like)
                        ->orWhere('k.nm_klu', 'like', $like);
                });
            });
    }
}
