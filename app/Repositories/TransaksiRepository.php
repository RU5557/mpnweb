<?php

namespace App\Repositories;

use App\Models\DetilTransaksiWp;
use App\Models\Klu;
use App\Models\MasterfileWp;
use App\Models\Pegawai;
use App\Models\Seksi;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiRepository
{
    /**
     * Ambil Opsi Dropdown Filter dengan Caching 24 Jam
     */
    public function getFilterDropdownOptions(int $tahun): array
    {
        return Cache::remember("transaksi_filter_options_{$tahun}", 86400, function () use ($tahun) {
            return [
                'listKota' => MasterfileWp::query()
                    ->whereNotNull('kota')
                    ->where('kota', '!=', '')
                    ->distinct()
                    ->orderBy('kota')
                    ->pluck('kota'),

                'listJenisWp' => MasterfileWp::query()
                    ->whereNotNull('jenis')
                    ->where('jenis', '!=', '')
                    ->distinct()
                    ->orderBy('jenis')
                    ->pluck('jenis'),

                'listSektor' => Klu::query()
                    ->whereNotNull('nm_kategori')
                    ->where('nm_kategori', '!=', '')
                    ->select('nm_kategori')
                    ->distinct()
                    ->orderBy('nm_kategori')
                    ->pluck('nm_kategori'),

                'listSeksi' => Seksi::query()
                    ->orderBy('nama')
                    ->get(['id', 'nama', 'kode']),

                'listAr' => Pegawai::query()
                    ->where('jabatan', '5')
                    ->where('tahun', $tahun)
                    ->orderBy('nama')
                    ->get(['nip', 'nama', 'seksi']),

                'listJs' => Pegawai::query()
                    ->where('jabatan', '11')
                    ->where('tahun', $tahun)
                    ->orderBy('nama')
                    ->get(['nip', 'nama', 'seksi']),

                'listTahunSetor' => DetilTransaksiWp::query()
                    ->whereNotNull('thn_setor')
                    ->distinct()
                    ->orderBy('thn_setor', 'desc')
                    ->pluck('thn_setor'),

                'listFungsi' => DetilTransaksiWp::query()
                    ->whereNotNull('fungsi')
                    ->where('fungsi', '!=', '')
                    ->distinct()
                    ->orderBy('fungsi')
                    ->pluck('fungsi'),
            ];
        });
    }

    /**
     * Reusable Query Builder untuk Transaksi (Pencarian & Export)
     */
    private function buildTransaksiQuery(array $filters, int $tahun = 2026): Builder
    {
        $query = DB::table('detil_transaksi_wp as t')
            ->leftJoin('masterfile_wp as m', 't.npwp15', '=', 'm.npwp15');

        // === FILTERING SISI TRANSAKSI ===

        // 1. Filter NPWP (9 / 15 Digit)
        if (! empty($filters['npwp'])) {
            $cleanNpwp = preg_replace('/[^0-9]/', '', $filters['npwp']);
            if (strlen($cleanNpwp) >= 15) {
                $npwp15 = substr($cleanNpwp, 0, 15);
                $query->where('t.npwp15', $npwp15);
            } else {
                $query->where('t.npwp', 'LIKE', $cleanNpwp.'%');
            }
        }

        // 2. Filter Nama WP
        if (! empty($filters['nama'])) {
            $nama = trim($filters['nama']);
            if (strlen($nama) >= 3) {
                $query->whereRaw('MATCH(t.nama_wp) AGAINST(? IN BOOLEAN MODE)', [$nama.'*']);
            } else {
                $query->where('t.nama_wp', 'LIKE', '%'.$nama.'%');
            }
        }

        // 3. Kode Map & Kode Bayar
        if (! empty($filters['kd_map'])) {
            $query->where('t.kd_map', trim($filters['kd_map']));
        }
        if (! empty($filters['kd_bayar'])) {
            $query->where('t.kd_bayar', trim($filters['kd_bayar']));
        }

        // 4. Tanggal Setor (Start & End)
        if (! empty($filters['tgl_setor_start']) && ! empty($filters['tgl_setor_end'])) {
            $query->whereBetween('t.tgl_setor', [$filters['tgl_setor_start'], $filters['tgl_setor_end']]);
        } elseif (! empty($filters['tgl_setor_start'])) {
            $query->where('t.tgl_setor', '>=', $filters['tgl_setor_start']);
        } elseif (! empty($filters['tgl_setor_end'])) {
            $query->where('t.tgl_setor', '<=', $filters['tgl_setor_end']);
        }

        // 5. Filter Tahun Setor & Bulan Setor
        if (! empty($filters['thn_setor']) && is_array($filters['thn_setor'])) {
            $query->whereIn('t.thn_setor', $filters['thn_setor']);
        }
        if (! empty($filters['bln_setor']) && is_array($filters['bln_setor'])) {
            $query->whereIn('t.bln_setor', $filters['bln_setor']);
        }

        // 6. NTPN
        if (! empty($filters['ntpn'])) {
            $query->where('t.ntpn', trim($filters['ntpn']));
        }

        // 7. Fungsi
        if (! empty($filters['fungsi']) && is_array($filters['fungsi'])) {
            $query->whereIn('t.fungsi', $filters['fungsi']);
        }

        // === FILTERING SISI MASTERFILE & PEGAWAI ===

        // 8. Kota
        if (! empty($filters['kota'])) {
            $query->where('m.kota', $filters['kota']);
        }

        // 9. Jenis WP
        if (! empty($filters['jenis_wp'])) {
            $query->where('m.jenis', $filters['jenis_wp']);
        }

        // 10. Sektor Usaha (KLU)
        if (! empty($filters['sektor'])) {
            $query->join('klu as k', 'm.klu', '=', 'k.kd_klu')
                ->where('k.nm_kategori', $filters['sektor']);
        }

        // 11. Seksi
        if (! empty($filters['seksi_id'])) {
            $seksi = Seksi::find($filters['seksi_id']);
            if ($seksi) {
                $query->join('pegawai as p_ar_filter', function ($join) use ($tahun) {
                    $join->on('m.nip_ar', '=', 'p_ar_filter.nip')
                        ->where('p_ar_filter.tahun', '=', $tahun)
                        ->where('p_ar_filter.jabatan', '=', '5');
                })->where('p_ar_filter.seksi', $seksi->nama);
            }
        }

        // 12. AR (Multi-select NIP)
        if (! empty($filters['nip_ar']) && is_array($filters['nip_ar'])) {
            $query->whereIn('m.nip_ar', $filters['nip_ar']);
        }

        // 13. JS (Multi-select NIP)
        if (! empty($filters['nip_js']) && is_array($filters['nip_js'])) {
            $query->whereIn('m.nip_js', $filters['nip_js']);
        }

        return $query;
    }

    /**
     * Pencarian Detil Transaksi / DRM Teroptimasi & Paginated
     */
    public function searchTransaksiPaginated(array $filters, int $perPage = 20, int $tahun = 2026): LengthAwarePaginator
    {
        $query = $this->buildTransaksiQuery($filters, $tahun);

        // Ambil data AR & JS via LEFT JOIN khusus untuk tampilan Paginated
        $query->leftJoin('pegawai as ar', function ($join) use ($tahun) {
            $join->on('m.nip_ar', '=', 'ar.nip')
                ->where('ar.tahun', '=', $tahun)
                ->where('ar.jabatan', '=', '5');
        })
            ->leftJoin('pegawai as js', function ($join) use ($tahun) {
                $join->on('m.nip_js', '=', 'js.nip')
                    ->where('js.tahun', '=', $tahun)
                    ->where('js.jabatan', '=', '11');
            });

        $query->select([
            't.id', 't.tgl_setor', 't.npwp15', 't.npwp', 't.nama_wp',
            'm.nama as nama_master', 't.fungsi', 't.kd_map', 't.kd_bayar',
            't.jenis as jenis_pajak', 't.masa_pajak', 't.thn_pajak', 't.jml_setor',
            't.ntpn', 'ar.nama as nama_ar', 'js.nama as nama_js', 'm.kota', 'm.jenis as jenis_wp',
        ]);

        // SORTING
        $sortBy = $filters['sort_by'] ?? 't.tgl_setor';
        $sortOrder = strtolower($filters['sort_order'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = [
            'tgl_setor' => 't.tgl_setor',
            'npwp15' => 't.npwp15',
            'fungsi' => 't.fungsi',
            'kd_map' => 't.kd_map',
            'jml_setor' => 't.jml_setor',
        ];

        $sortColumn = $allowedSorts[$sortBy] ?? 't.tgl_setor';
        $query->orderBy($sortColumn, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Streaming CSV Export untuk Data Transaksi / DRM (Cepat, Mengikuti Filter & Memory-Efficient)
     */
    public function exportTransaksiCsv(array $filters, int $tahun = 2026): StreamedResponse
    {
        $fileName = 'export_transaksi_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // 1. Pre-load Pegawai Map ke Memory Cache untuk menghilangkan JOIN saat Export
        $pegawaiMap = Cache::remember("pegawai_map_{$tahun}", 3600, function () use ($tahun) {
            return Pegawai::where('tahun', $tahun)->pluck('nama', 'nip')->toArray();
        });

        return response()->stream(function () use ($filters, $tahun, $pegawaiMap) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Microsoft Excel

            // Header CSV
            fputcsv($file, [
                'TGL SETOR', 'NPWP15', 'NAMA WP', 'FUNGSI', 'KD MAP', 'KD BAYAR',
                'MASA PAJAK', 'THN PAJAK', 'JUMLAH SETOR', 'NTPN', 'NAMA AR', 'NAMA JS', 'KOTA',
            ]);

            // 2. Query mematuhi filter pencarian yang diinput user
            $query = $this->buildTransaksiQuery($filters, $tahun)
                ->select([
                    't.tgl_setor', 't.npwp15', 't.nama_wp', 'm.nama as nama_master',
                    't.fungsi', 't.kd_map', 't.kd_bayar', 't.masa_pajak', 't.thn_pajak',
                    't.jml_setor', 't.ntpn', 'm.nip_ar', 'm.nip_js', 'm.kota',
                ]);

            // Sorting default
            $query->orderBy('t.tgl_setor', 'desc');

            $index = 0;
            // 3. Gunakan cursor() untuk streaming langsung
            foreach ($query->cursor() as $row) {
                $namaAr = ! empty($row->nip_ar) ? ($pegawaiMap[$row->nip_ar] ?? '-') : '-';
                $namaJs = ! empty($row->nip_js) ? ($pegawaiMap[$row->nip_js] ?? '-') : '-';

                fputcsv($file, [
                    $row->tgl_setor,
                    ! empty($row->npwp15) ? $row->npwp15 : '',
                    $row->nama_wp ?? $row->nama_master,
                    $row->fungsi,
                    $row->kd_map,
                    $row->kd_bayar,
                    $row->masa_pajak,
                    $row->thn_pajak,
                    $row->jml_setor,
                    $row->ntpn,
                    $namaAr,
                    $namaJs,
                    $row->kota ?? '-',
                ]);

                $index++;
                if ($index % 1000 === 0 && ob_get_level() > 0) {
                    ob_flush();
                    flush();
                }
            }

            fclose($file);
        }, 200, $headers);
    }
}
