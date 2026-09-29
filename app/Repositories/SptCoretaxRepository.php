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

    /**
     * Reusable Query Builder untuk Pencarian & Export SPT Coretax
     */
    private function buildSptQuery(array $filters): Builder
    {
        $query = DB::table('spt_coretax')
            // Join ke Masterfile WP berbasis NPWP 16 Digit
            ->leftJoin('masterfile_wp', 'spt_coretax.npwp', '=', 'masterfile_wp.npwp16')
            // Join ke Pegawai berbasis NIP AR (jabatan = '5') & Tahun
            ->leftJoin('pegawai', function ($join) {
                $join->on('masterfile_wp.nip_ar', '=', 'pegawai.nip')
                    ->on('spt_coretax.tahun', '=', 'pegawai.tahun')
                    ->where('pegawai.jabatan', '=', '5');
            });

        // 1. Filter NPWP
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

        // 2. Filter Nama Wajib Pajak (FULLTEXT INDEX)
        if (! empty($filters['nama'])) {
            $searchTerm = trim($filters['nama']);
            $query->whereRaw('MATCH(spt_coretax.nama) AGAINST(? IN BOOLEAN MODE)', ["*{$searchTerm}*"]);
        }

        // 3. Filter Masa Pajak
        if (! empty($filters['masa1'])) {
            $query->where('spt_coretax.masa1', '>=', sprintf('%02d', $filters['masa1']));
        }
        if (! empty($filters['masa2'])) {
            $query->where('spt_coretax.masa2', '<=', sprintf('%02d', $filters['masa2']));
        }

        // 4. Filter Tahun Pajak
        if (! empty($filters['thn_pajak'])) {
            $query->where('spt_coretax.thn_pajak', $filters['thn_pajak']);
        }

        // 5. Filter Pembetulan (String Match: Normal, Pembetulan 1, dll)
        if (! empty($filters['pembetulan'])) {
            $query->where('spt_coretax.pembetulan', trim($filters['pembetulan']));
        }

        // 6. Filter Jenis SPT
        if (! empty($filters['jenis_spt'])) {
            $query->where('spt_coretax.jenis_spt', $filters['jenis_spt']);
        }

        // 7. Filter Status SPT
        if (! empty($filters['status_spt'])) {
            $query->where('spt_coretax.status_spt', $filters['status_spt']);
        }

        // 8. Filter Tanggal Terima (Range)
        if (! empty($filters['tgl_terima_mulai'])) {
            $query->where('spt_coretax.tgl_terima', '>=', $filters['tgl_terima_mulai'].' 00:00:00');
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->where('spt_coretax.tgl_terima', '<=', $filters['tgl_terima_selesai'].' 23:59:59');
        }

        // 9. Filter AR (NIP AR)
        if (! empty($filters['nip_ar'])) {
            $query->where('masterfile_wp.nip_ar', $filters['nip_ar']);
        }

        return $query;
    }

    /**
     * Pencarian SPT Coretax Paginated
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
     * Streaming CSV Export untuk Data SPT Coretax
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
                    ! empty($row->npwp) ? "'".$row->npwp : '', // Tambahkan kutip agar tidak terpotong di Excel
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
            ->where('jabatan', '5') // Hanya Account Representative
            ->where('tahun', $tahun)
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->groupBy('nip', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
    }
}
