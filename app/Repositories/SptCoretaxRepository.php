<?php

namespace App\Repositories;

use App\Traits\CanStreamCsv;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SptCoretaxRepository
{
    use CanStreamCsv;

    /* =========================================================================
     | 1. METODE PENCARIAN & EXPORT DETAIL SPT (SptSearchController)
     | ========================================================================= */

    /**
     * Reusable Query Builder untuk Pencarian Detail SPT Coretax
     */
    private function buildSptQuery(array $filters): Builder
    {
        $query = DB::table('spt_coretax')
            ->leftJoin('masterfile_wp', 'spt_coretax.npwp', '=', 'masterfile_wp.npwp16')
            ->leftJoin('pegawai', function ($join) {
                $join->on('masterfile_wp.nip_ar', '=', 'pegawai.nip')
                    ->on('spt_coretax.tahun', '=', 'pegawai.tahun')
                    ->where('pegawai.jabatan', '=', '5');
            });

        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', $filters['npwp']);
            if (strlen($cleanNpwp) === 16) {
                $query->where('spt_coretax.npwp', $cleanNpwp);
            } else {
                $query->where(function ($q) use ($cleanNpwp) {
                    $q->where('spt_coretax.npwp', 'LIKE', $cleanNpwp.'%')
                        ->orWhere('masterfile_wp.npwp15', 'LIKE', $cleanNpwp.'%');
                });
            }
        }

        if (! empty($filters['nama'])) {
            $searchTerm = trim($filters['nama']);
            $query->whereRaw('MATCH(spt_coretax.nama) AGAINST(? IN BOOLEAN MODE)', ["*{$searchTerm}*"]);
        }

        if (! empty($filters['masa1'])) {
            $query->where('spt_coretax.masa1', '>=', sprintf('%02d', $filters['masa1']));
        }
        if (! empty($filters['masa2'])) {
            $query->where('spt_coretax.masa2', '<=', sprintf('%02d', $filters['masa2']));
        }

        if (! empty($filters['thn_pajak'])) {
            $query->where('spt_coretax.thn_pajak', $filters['thn_pajak']);
        }

        if (! empty($filters['pembetulan'])) {
            $query->where('spt_coretax.pembetulan', trim($filters['pembetulan']));
        }

        if (! empty($filters['jenis_spt'])) {
            $query->where('spt_coretax.jenis_spt', $filters['jenis_spt']);
        }

        if (! empty($filters['status_spt'])) {
            $query->where('spt_coretax.status_spt', $filters['status_spt']);
        }

        if (! empty($filters['tgl_terima_mulai'])) {
            $query->where('spt_coretax.tgl_terima', '>=', $filters['tgl_terima_mulai'].' 00:00:00');
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->where('spt_coretax.tgl_terima', '<=', $filters['tgl_terima_selesai'].' 23:59:59');
        }

        if (! empty($filters['nip_ar'])) {
            $query->where('masterfile_wp.nip_ar', $filters['nip_ar']);
        }

        return $query;
    }

    /**
     * Pencarian SPT Coretax Paginated (SptSearchController)
     */
    public function searchSpt(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildSptQuery($filters)
            ->select([
                'spt_coretax.nomor_tanda_terima',
                'spt_coretax.kanal_pelaporan',
                'spt_coretax.nama',
                'spt_coretax.npwp',
                'spt_coretax.jenis_spt',
                'spt_coretax.status_spt',
                'spt_coretax.masa1',
                'spt_coretax.masa2',
                'spt_coretax.thn_pajak',
                'spt_coretax.pembetulan',
                'spt_coretax.tgl_terima',
                'masterfile_wp.nip_ar',
                'pegawai.nama as nama_ar',
            ])
            ->orderBy('spt_coretax.tgl_terima', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Export CSV Data Detail Raw SPT (SptSearchController)
     */
    public function exportSptCsv(array $filters): StreamedResponse
    {
        $filename = 'export_spt_coretax_'.date('Ymd_His').'.csv';

        $csvHeaders = [
            'NO BPE / TANDA TERIMA',
            'KANAL PELAPORAN',
            'NPWP',
            'NAMA WAJIB PAJAK',
            'JENIS SPT',
            'STATUS SPT',
            'MASA AWAL',
            'MASA AKHIR',
            'THN PAJAK',
            'PEMBETULAN',
            'TGL TERIMA',
            'NIP AR',
            'NAMA AR',
        ];

        return $this->streamCsvResponse($filename, $csvHeaders, function ($file) use ($filters) {
            $query = $this->buildSptQuery($filters)
                ->select([
                    'spt_coretax.nomor_tanda_terima',
                    'spt_coretax.kanal_pelaporan',
                    'spt_coretax.npwp',
                    'spt_coretax.nama',
                    'spt_coretax.jenis_spt',
                    'spt_coretax.status_spt',
                    'spt_coretax.masa1',
                    'spt_coretax.masa2',
                    'spt_coretax.thn_pajak',
                    'spt_coretax.pembetulan',
                    'spt_coretax.tgl_terima',
                    'masterfile_wp.nip_ar',
                    'pegawai.nama as nama_ar',
                ])
                ->orderBy('spt_coretax.tgl_terima', 'desc');

            $index = 0;

            foreach ($query->cursor() as $row) {
                fputcsv($file, [
                    $row->nomor_tanda_terima ?? '-',
                    $row->kanal_pelaporan ?? '-',
                    ! empty($row->npwp) ? "'".$row->npwp : '',
                    $row->nama ?? '-',
                    $row->jenis_spt ?? '-',
                    $row->status_spt ?? '-',
                    $row->masa1 ?? '-',
                    $row->masa2 ?? '-',
                    $row->thn_pajak ?? '-',
                    $row->pembetulan ?? 'Normal',
                    $row->tgl_terima ?? '-',
                    ! empty($row->nip_ar) ? "'".$row->nip_ar : '-',
                    $row->nama_ar ?? 'Unassigned',
                ]);

                $index++;
                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }
        });
    }

    /* =========================================================================
     | 2. METODE MATRIX PIVOT KEPATUHAN (KepatuhanSptController)
     | ========================================================================= */

    /**
     * Reusable Builder khusus Matriks Kepatuhan SPT
     */
    private function getPivotKepatuhanQuery(array $filters): Builder
    {
        $query = DB::table('spt_coretax')
            ->where('spt_coretax.status_spt', '=', 'submitted')
            ->where(function ($q) {
                $q->where('spt_coretax.pembetulan', '=', '0')
                    ->orWhere('spt_coretax.pembetulan', '=', 'Normal')
                    ->orWhereNull('spt_coretax.pembetulan');
            })
            ->leftJoin('masterfile_wp', 'spt_coretax.npwp', '=', 'masterfile_wp.npwp16')
            ->leftJoin('pegawai', function ($join) {
                $join->on('masterfile_wp.nip_ar', '=', 'pegawai.nip')
                    ->on('spt_coretax.tahun', '=', 'pegawai.tahun')
                    ->where('pegawai.jabatan', '=', '5');
            });

        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', $filters['npwp']);
            if (strlen($cleanNpwp) === 16) {
                $query->where(function ($q) use ($cleanNpwp) {
                    $q->where('spt_coretax.npwp', $cleanNpwp)
                        ->orWhere('masterfile_wp.npwp16', $cleanNpwp);
                });
            } else {
                $query->where(function ($q) use ($cleanNpwp) {
                    $q->where('spt_coretax.npwp', 'LIKE', $cleanNpwp.'%')
                        ->orWhere('masterfile_wp.npwp15', 'LIKE', $cleanNpwp.'%');
                });
            }
        }

        if (! empty($filters['nama'])) {
            $searchTerm = trim($filters['nama']);
            $query->whereRaw('MATCH(spt_coretax.nama) AGAINST(? IN BOOLEAN MODE)', ["*{$searchTerm}*"]);
        }

        if (! empty($filters['jenis_spt'])) {
            $query->where('spt_coretax.jenis_spt', $filters['jenis_spt']);
        }

        if (! empty($filters['tgl_terima_mulai'])) {
            $query->where('spt_coretax.tgl_terima', '>=', $filters['tgl_terima_mulai'].' 00:00:00');
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->where('spt_coretax.tgl_terima', '<=', $filters['tgl_terima_selesai'].' 23:59:59');
        }

        if (! empty($filters['thn_pajak'])) {
            $query->where('spt_coretax.thn_pajak', $filters['thn_pajak']);
        }

        if (! empty($filters['nip_ar'])) {
            $query->where('masterfile_wp.nip_ar', $filters['nip_ar']);
        }

        return $query;
    }

    /**
     * Query Matrix Pivot SPT Paginated (KepatuhanSptController)
     */
    public function getMatrixKepatuhanData(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->getPivotKepatuhanQuery($filters);

        $selects = [
            'spt_coretax.npwp',
            'spt_coretax.nama',
            'spt_coretax.jenis_spt',
            'spt_coretax.thn_pajak',
        ];

        for ($m = 1; $m <= 12; $m++) {
            $mStr = sprintf('%02d', $m);
            $selects[] = DB::raw("MAX(CASE WHEN CAST(spt_coretax.masa1 AS UNSIGNED) <= {$m} AND CAST(spt_coretax.masa2 AS UNSIGNED) >= {$m} THEN spt_coretax.tgl_terima END) as m_{$mStr}");
        }

        return $query->select($selects)
            ->groupBy('spt_coretax.npwp', 'spt_coretax.nama', 'spt_coretax.jenis_spt', 'spt_coretax.thn_pajak')
            ->orderBy('spt_coretax.nama', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Export CSV Matriks Kepatuhan Masa 1-12 (KepatuhanSptController)
     */
    public function exportPivotCsv(array $filters): StreamedResponse
    {
        $filename = 'matrix_kepatuhan_spt_'.date('Ymd_His').'.csv';

        $headers = [
            'NPWP',
            'NAMA WAJIB PAJAK',
            'JENIS SPT',
            'THN PAJAK',
            'MASA 1',
            'MASA 2',
            'MASA 3',
            'MASA 4',
            'MASA 5',
            'MASA 6',
            'MASA 7',
            'MASA 8',
            'MASA 9',
            'MASA 10',
            'MASA 11',
            'MASA 12',
        ];

        return $this->streamCsvResponse($filename, $headers, function ($file) use ($filters) {
            $query = $this->getPivotKepatuhanQuery($filters);

            $selects = [
                'spt_coretax.npwp',
                'spt_coretax.nama',
                'spt_coretax.jenis_spt',
                'spt_coretax.thn_pajak',
            ];

            for ($m = 1; $m <= 12; $m++) {
                $mStr = sprintf('%02d', $m);
                $selects[] = DB::raw("MAX(CASE WHEN CAST(spt_coretax.masa1 AS UNSIGNED) <= {$m} AND CAST(spt_coretax.masa2 AS UNSIGNED) >= {$m} THEN spt_coretax.tgl_terima END) as m_{$mStr}");
            }

            $query->select($selects)
                ->groupBy('spt_coretax.npwp', 'spt_coretax.nama', 'spt_coretax.jenis_spt', 'spt_coretax.thn_pajak')
                ->orderBy('spt_coretax.nama', 'asc');

            $index = 0;

            foreach ($query->cursor() as $row) {
                $csvRow = [
                    ! empty($row->npwp) ? "'".$row->npwp : '-',
                    $row->nama ?? '-',
                    $row->jenis_spt ?? '-',
                    $row->thn_pajak ?? '-',
                ];

                for ($m = 1; $m <= 12; $m++) {
                    $colKey = 'm_'.sprintf('%02d', $m);
                    $valDate = $row->$colKey;
                    $csvRow[] = $valDate ? date('Y-m-d', strtotime($valDate)) : '-';
                }

                fputcsv($file, $csvRow);

                $index++;
                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }
        });
    }

    /* =========================================================================
     | 3. HELPER DROPDOWN OPTIONS
     | ========================================================================= */

    public function getDistinctJenisSpt(): Collection
    {
        return DB::table('spt_coretax')
            ->whereNotNull('jenis_spt')
            ->where('jenis_spt', '!=', '')
            ->distinct()
            ->orderBy('jenis_spt')
            ->pluck('jenis_spt');
    }

    public function getDistinctStatusSpt(): Collection
    {
        return DB::table('spt_coretax')
            ->whereNotNull('status_spt')
            ->where('status_spt', '!=', '')
            ->distinct()
            ->orderBy('status_spt')
            ->pluck('status_spt');
    }

    public function getDistinctPembetulan(): Collection
    {
        return DB::table('spt_coretax')
            ->whereNotNull('pembetulan')
            ->where('pembetulan', '!=', '')
            ->distinct()
            ->orderBy('pembetulan')
            ->pluck('pembetulan');
    }

    public function getDistinctAR(int $tahun = 2026): Collection
    {
        return DB::table('pegawai')
            ->select('nip', 'nama')
            ->where('jabatan', '5')
            ->where('tahun', $tahun)
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->groupBy('nip', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
    }
}
