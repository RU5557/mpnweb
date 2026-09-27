<?php

namespace App\Repositories;

use App\Models\MasterfileWp;
use App\Models\Pegawai;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WpRepository
{
    /**
     * Mendapatkan Cache Option untuk Dropdown Filter
     */
    public function getFilterDropdownOptions(int $tahun): array
    {
        return [
            'listKlu' => Cache::remember('mf_filter_klu', 3600, fn () => MasterfileWp::whereNotNull('klu')->where('klu', '!=', '')->distinct()->orderBy('klu', 'asc')->pluck('klu')->toArray()
            ),
            'listKelurahan' => Cache::remember('mf_filter_kelurahan', 3600, fn () => MasterfileWp::whereNotNull('kelurahan')->where('kelurahan', '!=', '')->distinct()->orderBy('kelurahan', 'asc')->pluck('kelurahan')->toArray()
            ),
            'listKecamatan' => Cache::remember('mf_filter_kecamatan', 3600, fn () => MasterfileWp::whereNotNull('kecamatan')->where('kecamatan', '!=', '')->distinct()->orderBy('kecamatan', 'asc')->pluck('kecamatan')->toArray()
            ),
            'listJenis' => Cache::remember('mf_filter_jenis', 3600, fn () => MasterfileWp::whereNotNull('jenis')->where('jenis', '!=', '')->distinct()->orderBy('jenis', 'asc')->pluck('jenis')->toArray()
            ),
            'listStatus' => Cache::remember('mf_filter_status', 3600, fn () => MasterfileWp::whereNotNull('status')->where('status', '!=', '')->distinct()->orderBy('status', 'asc')->pluck('status')->toArray()
            ),
            'listAr' => Cache::remember('filter_ar_jabatan_5_'.$tahun, 3600, fn () => Pegawai::where('jabatan', 5)->where('tahun', $tahun)->select('nip', 'nama')->orderBy('nama', 'asc')->get()
            ),
            'listJs' => Cache::remember('filter_js_jabatan_11_'.$tahun, 3600, fn () => Pegawai::where('jabatan', 11)->where('tahun', $tahun)->select('nip', 'nama')->orderBy('nama', 'asc')->get()
            ),
        ];
    }

    /**
     * Build Base Query Masterfile WP dengan seluruh filter
     */
    public function buildMasterfileQuery(array $filters): Builder
    {
        $query = MasterfileWp::query();

        // 1. Filter NPWP Presisi (Prefix Search)
        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', (string) $filters['npwp']);
            if ($cleanNpwp !== '') {
                $query->where(function ($q) use ($cleanNpwp) {
                    $q->where('npwp15', 'LIKE', "{$cleanNpwp}%")
                        ->orWhere('npwp16', 'LIKE', "{$cleanNpwp}%");
                });
            }
        }

        // 2. Filter Fulltext Match Nama WP
        if (! empty($filters['nama'])) {
            $nama = trim((string) $filters['nama']);
            $words = array_filter(explode(' ', $nama));
            if (! empty($words)) {
                $searchPhrase = '+'.implode(' +', $words).'*';
                $query->whereRaw('MATCH(nama) AGAINST(? IN BOOLEAN MODE)', [$searchPhrase]);
            }
        }

        // 3. Filter Exact Match (Wilayah & Status)
        if (! empty($filters['klu'])) {
            $query->where('klu', $filters['klu']);
        }
        if (! empty($filters['kelurahan'])) {
            $query->where('kelurahan', $filters['kelurahan']);
        }
        if (! empty($filters['kecamatan'])) {
            $query->where('kecamatan', $filters['kecamatan']);
        }
        if (! empty($filters['jenis'])) {
            $query->where('jenis', $filters['jenis']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // 4. Filter Range Tanggal Daftar
        $tglAwal = $filters['tgl_daftar_awal'] ?? null;
        $tglAkhir = $filters['tgl_daftar_akhir'] ?? null;

        if (! empty($tglAwal) && ! empty($tglAkhir)) {
            $query->whereBetween('tanggal_daftar', [$tglAwal, $tglAkhir]);
        } elseif (! empty($tglAwal)) {
            $query->where('tanggal_daftar', '>=', $tglAwal);
        } elseif (! empty($tglAkhir)) {
            $query->where('tanggal_daftar', '<=', $tglAkhir);
        }

        // 5. Filter AR & JS
        if (! empty($filters['nip_ar'])) {
            $query->where('nip_ar', $filters['nip_ar']);
        }
        if (! empty($filters['nip_js'])) {
            $query->where('nip_js', $filters['nip_js']);
        }

        return $query;
    }

    /**
     * Pencarian Masterfile WP dengan Paginasi Cepat
     */
    public function searchMasterfilePaginated(array $filters, int $perPage = 20, ?int $tahun = null): LengthAwarePaginator
    {
        $tahun = $tahun ?? (int) date('Y');
        $query = $this->buildMasterfileQuery($filters);

        // Sorting
        $allowedSorts = ['npwp15', 'nama', 'jenis', 'tanggal_daftar'];
        $sortBy = $filters['sort_by'] ?? 'nama';
        $sortOrder = strtolower($filters['sort_order'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('nama', 'asc');
        }

        // Execute Pagination
        $results = $query->paginate($perPage);

        // Eager Load Relasi AR & JS HANYA untuk item yang terpilih di halaman aktif
        $results->getCollection()->load([
            'ar' => fn ($q) => $q->where('tahun', $tahun),
            'js' => fn ($q) => $q->where('tahun', $tahun),
        ]);

        return $results;
    }

    /**
     * Export CSV Masterfile WP menggunakan Stream & Cursor
     */
    public function exportMasterfileCsv(array $filters, ?int $tahun = null): StreamedResponse
    {
        $tahun = $tahun ?? (int) date('Y');
        $fileName = 'export_masterfile_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($filters, $tahun) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($file, ['NPWP', 'NPWP15', 'NPWP16', 'Nama WP', 'KLU', 'Alamat', 'Kelurahan', 'Kecamatan', 'Jenis WP', 'Status WP', 'Tgl Daftar', 'AR', 'JS']);

            // Menggunakan base query yang sama, ditambah Left Join Pegawai
            $query = $this->buildMasterfileQuery($filters)
                ->leftJoin('pegawai as p_ar', function ($join) use ($tahun) {
                    $join->on('masterfile_wp.nip_ar', '=', 'p_ar.nip')->where('p_ar.tahun', '=', $tahun);
                })
                ->leftJoin('pegawai as p_js', function ($join) use ($tahun) {
                    $join->on('masterfile_wp.nip_js', '=', 'p_js.nip')->where('p_js.tahun', '=', $tahun);
                })
                ->select('masterfile_wp.*', 'p_ar.nama as nama_ar', 'p_js.nama as nama_js');

            // Gunakan cursor() agar efisien penggunaan memori (streaming ribuan data)
            foreach ($query->cursor() as $item) {
                fputcsv($file, [
                    $item->npwp ?? $item->npwp15,
                    $item->npwp15,
                    $item->npwp16,
                    $item->nama,
                    $item->klu,
                    $item->alamat,
                    $item->kelurahan,
                    $item->kecamatan,
                    $item->jenis,
                    $item->status,
                    $item->tanggal_daftar,
                    $item->nama_ar,
                    $item->nama_js,
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
