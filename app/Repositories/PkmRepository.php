<?php

namespace App\Repositories;

use App\Models\DetilTransaksiWp;
use App\Traits\CanStreamCsv;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmRepository
{
    use CanStreamCsv;

    // ==========================================
    // 1. SECTION: PKM PENGAWASAN
    // ==========================================

    public function getDaftarSeksiPengawasan(): array
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

    public function getSummaryPengawasan(int $tahun, int $bulan, string $seksiFilter, string $sortColumn, string $sortDirection): Collection
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
            return $this->buildQueryPengawasan($tahun, $bulan, $seksiFilter)
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
                ->when($sortColumn === 'nama_seksi', fn ($q) => $q->orderBy(DB::raw("COALESCE(p.nama, 'Unassign')"), 'asc'))
                ->get();
        });
    }

    public function exportPengawasanCsv(int $tahun, int $bulan, string $seksiFilter): StreamedResponse
    {
        $filename = "Export_Detil_PKM_Pengawasan_{$tahun}_{$bulan}.csv";
        $headers = ['NO', 'NPWP', 'NAMA WP', 'SEKSI', 'NAMA AR', 'FUNGSI', 'KD MAP', 'KD BAYAR', 'BULAN', 'TAHUN', 'JUMLAH SETOR'];

        return $this->streamCsvResponse($filename, $headers, function ($file) use ($tahun, $bulan, $seksiFilter) {
            $query = $this->buildQueryPengawasan($tahun, $bulan, $seksiFilter)
                ->select([
                    'dt.npwp15',
                    DB::raw("COALESCE(mw.nama, '-') as nama_wp"),
                    'dt.kd_map', 'dt.kd_bayar', 'dt.jml_setor', 'dt.thn_setor', 'dt.bln_setor', 'dt.fungsi',
                    DB::raw("COALESCE(mw.nip_ar, 'Unassign') as nip_ar"),
                    DB::raw("COALESCE(p.nama, 'Unassign') as nama_ar"),
                    DB::raw("COALESCE(s.nama, 'Unassign') as nama_seksi"),
                ])
                ->orderBy('s.nama', 'asc')
                ->orderBy('p.nama', 'asc');

            $index = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $index++, ! empty($row->npwp15) ? $row->npwp15 : '',
                    $row->nama_wp, $row->nama_seksi, $row->nama_ar,
                    $row->fungsi, $row->kd_map, $row->kd_bayar,
                    $row->bln_setor, $row->thn_setor, $row->jml_setor,
                ]);

                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }
        });
    }

    private function buildQueryPengawasan(int $tahun, int $bulan, string $seksiFilter)
    {
        $tahunSaatIni = (int) date('Y');

        return DetilTransaksiWp::query()
            ->toBase()
            ->from('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoin('pegawai as p', fn ($join) => $join->on('mw.nip_ar', '=', 'p.nip')->where('p.tahun', '=', $tahunSaatIni))
            ->leftJoin('seksi as s', 'p.seksi', '=', 's.id')
            ->whereIn('dt.fungsi', ['akt pengawasan', 'lainnya', 'wra pengawasan'])
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->when($seksiFilter !== '', function ($q) use ($seksiFilter) {
                return $seksiFilter === 'Unassign' ? $q->whereNull('s.nama') : $q->where('s.nama', $seksiFilter);
            });
    }

    // ==========================================
    // 2. SECTION: PKM PEMERIKSAAN
    // ==========================================

    public function getPaginatedPemeriksaan(
        int $tahun, int $bulan, string $search, string $sortColumn, string $sortDirection, int $page = 1, int $perPage = 10
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
            return $this->buildQueryPemeriksaan($tahun, $bulan, $search)
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

    public function exportPemeriksaanCsv(int $tahun, int $bulan, string $search): StreamedResponse
    {
        $filename = "Export_Detil_PKM_Pemeriksaan_{$tahun}_{$bulan}.csv";
        $headers = ['NO', 'NPWP', 'NAMA WP', 'KD KLU', 'NAMA KLU', 'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR'];

        return $this->streamCsvResponse($filename, $headers, function ($file) use ($tahun, $bulan, $search) {
            $query = $this->buildQueryPemeriksaan($tahun, $bulan, $search)
                ->select([
                    'dt.npwp15',
                    DB::raw("COALESCE(mw.nama, 'WP Tidak Terdaftar') as nama_wp"),
                    DB::raw("COALESCE(mw.klu, '-') as kd_klu"),
                    DB::raw("COALESCE(k.nm_klu, '-') as nm_klu"),
                    'dt.kd_map', 'dt.kd_bayar', 'dt.jml_setor', 'dt.bln_setor', 'dt.thn_setor', 'dt.fungsi',
                ])
                ->orderBy('mw.nama', 'asc')
                ->orderBy('dt.bln_setor', 'asc');

            $index = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $index++, ! empty($row->npwp15) ? $row->npwp15 : '',
                    $row->nama_wp, $row->kd_klu, $row->nm_klu,
                    $row->kd_map, $row->kd_bayar, $row->fungsi,
                    $row->bln_setor, $row->thn_setor, $row->jml_setor,
                ]);

                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }
        });
    }

    private function buildQueryPemeriksaan(int $tahun, int $bulan, string $search)
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

    // ==========================================
    // 3. SECTION: PKM PENAGIHAN
    // ==========================================

    public function getSummaryPenagihan(int $tahun, int $bulan, string $dspcFilter, string $sortColumn, string $sortDirection): Collection
    {
        $allowedSorts = [
            'nip_jspn' => 'nip_jspn',
            'nama_jspn' => 'nama_jspn',
            'flag_skp' => 'flag_skp',
            'akt_penagihan' => 'akt_penagihan',
        ];

        $sortBy = $allowedSorts[$sortColumn] ?? 'nip_jspn';
        $sortDir = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';

        $cacheKey = "pkm_penagihan_v2_{$tahun}_{$bulan}_{$dspcFilter}_{$sortBy}_{$sortDir}";

        return Cache::remember($cacheKey, 600, function () use ($tahun, $bulan, $dspcFilter, $sortBy, $sortDir) {
            return $this->buildQueryPenagihan($tahun, $bulan, $dspcFilter)
                ->select([
                    DB::raw("COALESCE(mw.nip_js, 'Unassign') as nip_jspn"),
                    DB::raw("COALESCE(p.nama, 'Unassign') as nama_jspn"),
                    DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC') as flag_skp"),
                    DB::raw('SUM(dt.jml_setor) as akt_penagihan'),
                ])
                ->groupBy(
                    DB::raw("COALESCE(mw.nip_js, 'Unassign')"),
                    DB::raw("COALESCE(p.nama, 'Unassign')"),
                    DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC')")
                )
                ->orderBy($sortBy, $sortDir)
                ->get();
        });
    }

    public function exportPenagihanCsv(int $tahun, int $bulan, string $dspcFilter): StreamedResponse
    {
        $filename = "Export_Detil_PKM_Penagihan_{$tahun}_{$bulan}.csv";
        $headers = ['NO', 'NPWP', 'NAMA WP', 'NIP JSPN', 'NAMA JSPN', 'FLAG SKP', 'KD MAP', 'KD BAYAR', 'FUNGSI', 'BULAN', 'TAHUN', 'JUMLAH SETOR'];

        return $this->streamCsvResponse($filename, $headers, function ($file) use ($tahun, $bulan, $dspcFilter) {
            $query = $this->buildQueryPenagihan($tahun, $bulan, $dspcFilter)
                ->select([
                    'dt.npwp15',
                    DB::raw("COALESCE(mw.nama, '-') as nama_wp"),
                    DB::raw("COALESCE(mw.nip_js, 'Unassign') as nip_jspn"),
                    DB::raw("COALESCE(p.nama, 'Unassign') as nama_jspn"),
                    DB::raw("COALESCE(dt.flag_skp, 'NON-DSPC') as flag_skp"),
                    'dt.kd_map', 'dt.kd_bayar', 'dt.jml_setor', 'dt.bln_setor', 'dt.thn_setor', 'dt.fungsi',
                ])
                ->orderBy('p.nama', 'asc')
                ->orderBy('dt.bln_setor', 'asc');

            $index = 1;
            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $index++, ! empty($row->npwp15) ? $row->npwp15 : '',
                    $row->nama_wp, $row->nip_jspn, $row->nama_jspn, $row->flag_skp,
                    $row->kd_map, $row->kd_bayar, $row->fungsi,
                    $row->bln_setor, $row->thn_setor, $row->jml_setor,
                ]);

                if ($index % 2000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }
        });
    }

    private function buildQueryPenagihan(int $tahun, int $bulan, string $dspcFilter)
    {
        $subPegawai = DB::table('pegawai')->where('tahun', $tahun);

        return DetilTransaksiWp::query()
            ->toBase()
            ->from('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->leftJoinSub($subPegawai, 'p', fn ($join) => $join->on('mw.nip_js', '=', 'p.nip'))
            ->where('dt.fungsi', 'akt penagihan')
            ->where('dt.thn_setor', $tahun)
            ->whereBetween('dt.bln_setor', [1, $bulan])
            ->when($dspcFilter !== '', function ($query) use ($dspcFilter) {
                if ($dspcFilter === 'DSPC') {
                    return $query->where('dt.flag_skp', 'DSPC');
                }

                return $query->where(fn ($q) => $q->where('dt.flag_skp', '!=', 'DSPC')->orWhereNull('dt.flag_skp'));
            });
    }
}
