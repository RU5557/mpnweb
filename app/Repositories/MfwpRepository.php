<?php

namespace App\Repositories;

use App\Models\Klu;
use App\Models\Mfwp;
use App\Models\Pegawai;
use App\Models\Seksi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MfwpRepository
{
    /**
     * Membangun Query Utama Pencarian MFWP dengan Join AR, Seksi, dan JSPN
     */
    public function buildSearchQuery(array $filters): Builder
    {
        $tahunSekarang = date('Y');

        $query = Mfwp::query()
            ->select([
                'mfwp.*',
                'ar.nama as nama_ar',
                'jspn.nama as nama_jspn',
                'seksi.nama as nama_seksi',
            ])
            // Left Join AR (Jabatan = 5, Tahun ini)
            ->leftJoin('pegawai as ar', function ($join) use ($tahunSekarang) {
                $join->on('mfwp.nip_ar', '=', 'ar.nip')
                    ->where('ar.jabatan', '=', '5')
                    ->where('ar.tahun', '=', $tahunSekarang);
            })
            // Left Join Seksi berdasarkan pegawai.seksi = seksi.id
            ->leftJoin('seksi', 'ar.seksi', '=', 'seksi.id')
            // Left Join JSPN (Jabatan = 11, Tahun ini)
            ->leftJoin('pegawai as jspn', function ($join) use ($tahunSekarang) {
                $join->on('mfwp.nip_js', '=', 'jspn.nip')
                    ->where('jspn.jabatan', '=', '11')
                    ->where('jspn.tahun', '=', $tahunSekarang);
            });

        // 1. Filter NPWP (9 digit / 15 digit / 16 digit)
        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', $filters['npwp']);
            $len = strlen($cleanNpwp);

            if ($len === 9) {
                // mfwp.npwp (9 digit pertama/format lokal)
                $query->where('mfwp.npwp', 'like', "{$cleanNpwp}%");
            } elseif ($len === 16) {
                // mfwp.npwp16
                $query->where('mfwp.npwp16', $cleanNpwp);
            } else {
                // mfwp.npwp15 / fallback
                $query->where('mfwp.npwp15', 'like', "%{$cleanNpwp}%");
            }
        }

        // 2. Nama WP (Menggunakan FULLTEXT ft_nama atau LIKE fallback)
        if (! empty($filters['nama'])) {
            $nama = trim($filters['nama']);
            if (strlen($nama) >= 3) {
                $query->whereRaw('MATCH(mfwp.nama) AGAINST(? IN BOOLEAN MODE)', ["*{$nama}*"]);
            } else {
                $query->where('mfwp.nama', 'like', "%{$nama}%");
            }
        }

        // 3. KLU
        if (! empty($filters['kd_klu'])) {
            $query->where('mfwp.klu', $filters['kd_klu']);
        }

        // 4. Kelurahan & Kecamatan
        if (! empty($filters['kelurahan'])) {
            $query->where('mfwp.kelurahan', $filters['kelurahan']);
        }
        if (! empty($filters['kecamatan'])) {
            $query->where('mfwp.kecamatan', $filters['kecamatan']);
        }

        // 5. Jenis WP & Status WP
        if (! empty($filters['jenis'])) {
            $query->where('mfwp.jenis', $filters['jenis']);
        }
        if (! empty($filters['status'])) {
            $query->where('mfwp.status', $filters['status']);
        }

        // 6. Tanggal Daftar (Range: Awal - Akhir)
        if (! empty($filters['tgl_daftar_awal'])) {
            $query->where('mfwp.tanggal_daftar', '>=', $filters['tgl_daftar_awal']);
        }
        if (! empty($filters['tgl_daftar_akhir'])) {
            $query->where('mfwp.tanggal_daftar', '<=', $filters['tgl_daftar_akhir']);
        }

        // 7. AR & Seksi Override Logic
        if (! empty($filters['nip_ar'])) {
            // Jika AR dipilih, filter spesifik NIP AR (Otomatis seksi ikut dari join AR)
            $query->where('mfwp.nip_ar', $filters['nip_ar']);
        } elseif (! empty($filters['id_seksi'])) {
            // Jika AR kosong tapi Seksi dipilih, filter berdasarkan seksi.id
            $query->where('ar.seksi', $filters['id_seksi']);
        }

        // 8. JSPN
        if (! empty($filters['nip_js'])) {
            $query->where('mfwp.nip_js', $filters['nip_js']);
        }

        return $query;
    }

    /**
     * Memanggil Pagination untuk Halaman Web
     */
    public function searchPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->buildSearchQuery($filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Cursor untuk Export CSV Hemat Memori
     */
    public function getCursorForExport(array $filters)
    {
        return $this->buildSearchQuery($filters)->cursor();
    }

    // =========================================================================
    // HELPER METHODS UNTUK DROPDOWN/LOOKUP FILTER
    // =========================================================================

    public function getDistinctKelurahan(): array
    {
        return Mfwp::whereNotNull('kelurahan')
            ->where('kelurahan', '!=', '')
            ->distinct()
            ->orderBy('kelurahan', 'asc')
            ->pluck('kelurahan')
            ->toArray();
    }

    public function getDistinctKecamatan(): array
    {
        return Mfwp::whereNotNull('kecamatan')
            ->where('kecamatan', '!=', '')
            ->distinct()
            ->orderBy('kecamatan', 'asc')
            ->pluck('kecamatan')
            ->toArray();
    }

    public function getDistinctJenis(): array
    {
        return Mfwp::whereNotNull('jenis')
            ->where('jenis', '!=', '')
            ->distinct()
            ->pluck('jenis')
            ->toArray();
    }

    public function getDistinctStatus(): array
    {
        return Mfwp::whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->pluck('status')
            ->toArray();
    }

    /**
     * Opsi List AR (Jabatan = 5, Tahun ini) + Relasi Seksi
     */
    public function getArList()
    {
        $tahunSekarang = date('Y');

        return Pegawai::select('pegawai.nip', 'pegawai.nama', 'pegawai.seksi', 'seksi.nama as nama_seksi')
            ->leftJoin('seksi', 'pegawai.seksi', '=', 'seksi.id')
            ->where('pegawai.jabatan', '5')
            ->where('pegawai.tahun', $tahunSekarang)
            ->whereNotNull('pegawai.seksi')
            ->orderBy('pegawai.nama', 'asc')
            ->get();
    }

    /**
     * Opsi List Seksi Pengawasan
     */
    public function getSeksiPengawasanList()
    {
        return Seksi::where('nama', 'like', '%pengawasan%')
            ->orderBy('nama', 'asc')
            ->get();
    }

    /**
     * Opsi List JSPN (Jabatan = 11, Tahun ini)
     */
    public function getJspnList()
    {
        $tahunSekarang = date('Y');

        return Pegawai::where('jabatan', '11')
            ->where('tahun', $tahunSekarang)
            ->orderBy('nama', 'asc')
            ->get(['nip', 'nama']);
    }
}
