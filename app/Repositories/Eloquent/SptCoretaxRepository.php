<?php

namespace App\Repositories\Eloquent;

use App\Models\Pegawai;
use App\Models\SptCoretax;
use App\Repositories\Contracts\SptCoretaxRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SptCoretaxRepository implements SptCoretaxRepositoryInterface
{
    public function searchSpt(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        $query = SptCoretax::query()
            ->select([
                'spt_coretax.*',
                'masterfile_wp.npwp15 as wp_npwp15',
                'masterfile_wp.npwp as wp_npwp',
                'masterfile_wp.nip_ar',
                'pegawai.nama as nama_ar',
            ])
            ->leftJoin('masterfile_wp', 'spt_coretax.npwp', '=', 'masterfile_wp.npwp16')
            ->leftJoin('pegawai', function ($join) {
                $join->on('masterfile_wp.nip_ar', '=', 'pegawai.nip')
                    ->on('spt_coretax.tahun', '=', 'pegawai.tahun');
            });

        // 1. Filter NPWP (Flexibel 9 / 15 / 16 digit)
        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', $filters['npwp']);
            $query->where(function (Builder $q) use ($cleanNpwp) {
                $q->where('spt_coretax.npwp', 'LIKE', "%{$cleanNpwp}%")
                    ->orWhere('masterfile_wp.npwp15', 'LIKE', "%{$cleanNpwp}%")
                    ->orWhere('masterfile_wp.npwp', 'LIKE', "%{$cleanNpwp}%");
            });
        }

        // 2. Filter Nama WP
        if (! empty($filters['nama'])) {
            $query->where('spt_coretax.nama', 'LIKE', '%'.$filters['nama'].'%');
        }

        // 3. Filter Masa Pajak (Range)
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

        // 5. Filter Pembetulan
        if (isset($filters['pembetulan']) && $filters['pembetulan'] !== '') {
            $query->where('spt_coretax.pembetulan', $filters['pembetulan']);
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
            $query->whereDate('spt_coretax.tgl_terima', '>=', $filters['tgl_terima_mulai']);
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->whereDate('spt_coretax.tgl_terima', '<=', $filters['tgl_terima_selesai']);
        }

        // 9. Filter Nama AR / NIP AR
        if (! empty($filters['nip_ar'])) {
            $query->where('masterfile_wp.nip_ar', $filters['nip_ar']);
        }

        return $query->orderBy('spt_coretax.tgl_terima', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getDistinctJenisSpt(): Collection
    {
        return SptCoretax::query()
            ->whereNotNull('jenis_spt')
            ->distinct()
            ->pluck('jenis_spt');
    }

    public function getDistinctStatusSpt(): Collection
    {
        return SptCoretax::query()
            ->whereNotNull('status_spt')
            ->distinct()
            ->pluck('status_spt');
    }

    public function getDistinctAR(): Collection
    {
        return Pegawai::query()
            ->select('nip', 'nama')
            ->whereNotNull('nip')
            ->where('nip', '!=', '')
            ->groupBy('nip', 'nama')
            ->orderBy('nama', 'asc')
            ->get();
    }
}
