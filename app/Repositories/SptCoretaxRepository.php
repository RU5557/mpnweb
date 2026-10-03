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

    private function applyExactNpwpFilter(Builder $query, string $cleanNpwp): Builder
    {
        $len = strlen($cleanNpwp);

        return $query->where(function (Builder $q) use ($cleanNpwp, $len) {
            if ($len === 16) {
                $q->where('spt_coretax.npwp', $cleanNpwp);
            } elseif ($len === 15) {
                $q->where('masterfile_wp.npwp15', $cleanNpwp);
            } elseif ($len === 9) {
                $q->where('masterfile_wp.npwp', $cleanNpwp);
            } else {
                $q->where('masterfile_wp.npwp', $cleanNpwp)->orWhere('masterfile_wp.npwp15', 'LIKE', $cleanNpwp.'%')->orWhere('spt_coretax.npwp', 'LIKE', $cleanNpwp.'%');
            }
        });
    }

    private function buildSptQuery(array $filters): Builder
    {
        $query = DB::table('spt_coretax')
            ->leftJoin('masterfile_wp', 'spt_coretax.npwp', '=', 'masterfile_wp.npwp16')
            ->leftJoin('pegawai', function ($join) {
                $join->on('masterfile_wp.nip_ar', '=', 'pegawai.nip')->on('spt_coretax.tahun', '=', 'pegawai.tahun')->where('pegawai.jabatan', '=', '5');
            });
        if (! empty($filters['npwp'])) {
            $clean = preg_replace('/[^0-9]/', '', $filters['npwp']);
            if ($clean !== '') {
                $this->applyExactNpwpFilter($query, $clean);
            }
        }
        if (! empty($filters['nama'])) {
            $nama = trim($filters['nama']);
            if (mb_strlen($nama) >= 4) {
                $query->whereRaw('MATCH(spt_coretax.nama) AGAINST(? IN BOOLEAN MODE)', ["*{$nama}*"]);
            } else {
                $query->where('spt_coretax.nama', 'LIKE', $nama.'%');
            }
        }
        if (! empty($filters['masa1'])) {
            $query->where('spt_coretax.masa1_int', '>=', (int) $filters['masa1']);
        }
        if (! empty($filters['masa2'])) {
            $query->where('spt_coretax.masa2_int', '<=', (int) $filters['masa2']);
        }
        if (! empty($filters['thn_pajak'])) {
            $query->where('spt_coretax.thn_pajak', $filters['thn_pajak']);
        }
        if (! empty($filters['pembetulan'])) {
            $query->where('spt_coretax.pembetulan', $filters['pembetulan']);
        }
        if (! empty($filters['jenis_spt'])) {
            if ($filters['jenis_spt'] === '__NULL__') {
                $query->where(function (Builder $qq) {
                    $qq->whereNull('spt_coretax.jenis_spt')->orWhere('spt_coretax.jenis_spt', '');
                });
            } else {
                $query->where('spt_coretax.jenis_spt', $filters['jenis_spt']);
            }
        }
        if (! empty($filters['status_spt'])) {
            $query->where('spt_coretax.status_spt', $filters['status_spt']);
        }
        if (! empty($filters['tgl_terima_mulai'])) {
            $query->whereDate('spt_coretax.tgl_terima', '>=', $filters['tgl_terima_mulai']);
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->whereDate('spt_coretax.tgl_terima', '<=', $filters['tgl_terima_selesai']);
        }
        if (! empty($filters['nip_ar'])) {
            $query->where('masterfile_wp.nip_ar', $filters['nip_ar']);
        }

        return $query;
    }

    public function searchSpt(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildSptQuery($filters)->select(['spt_coretax.nomor_tanda_terima', 'spt_coretax.kanal_pelaporan', 'spt_coretax.nama', 'spt_coretax.npwp', 'spt_coretax.jenis_spt', 'spt_coretax.status_spt', 'spt_coretax.masa1', 'spt_coretax.masa2', 'spt_coretax.thn_pajak', 'spt_coretax.pembetulan', 'spt_coretax.tgl_terima', 'masterfile_wp.nip_ar', 'pegawai.nama as nama_ar'])->orderBy('spt_coretax.tgl_terima', 'desc')->paginate($perPage)->withQueryString();
    }

    public function exportSptCsv(array $filters): StreamedResponse
    {
        $filename = 'export_spt_coretax_'.date('Ymd_His').'.csv';
        $headers = ['NO BPE', 'KANAL', 'NPWP', 'NAMA', 'JENIS SPT', 'STATUS', 'MASA1', 'MASA2', 'THN PAJAK', 'PEMBETULAN', 'TGL TERIMA', 'NIP AR', 'NAMA AR'];

        return $this->streamCsvResponse($filename, $headers, function ($file) use ($filters) {
            foreach ($this->buildSptQuery($filters)->select(['spt_coretax.nomor_tanda_terima', 'spt_coretax.kanal_pelaporan', 'spt_coretax.npwp', 'spt_coretax.nama', 'spt_coretax.jenis_spt', 'spt_coretax.status_spt', 'spt_coretax.masa1', 'spt_coretax.masa2', 'spt_coretax.thn_pajak', 'spt_coretax.pembetulan', 'spt_coretax.tgl_terima', 'masterfile_wp.nip_ar', 'pegawai.nama as nama_ar'])->orderBy('spt_coretax.tgl_terima', 'desc')->cursor() as $r) {
                fputcsv($file, [$r->nomor_tanda_terima ?? '-', $r->kanal_pelaporan ?? '-', "'".$r->npwp, $r->nama ?? '-', $r->jenis_spt ?? '-', $r->status_spt ?? '-', $r->masa1 ?? '-', $r->masa2 ?? '-', $r->thn_pajak ?? '-', $r->pembetulan ?? '-', $r->tgl_terima ?? '-', "'".$r->nip_ar, $r->nama_ar ?? '-']);
            }
        });
    }

    private function getPivotKepatuhanQuery(array $filters): Builder
    {
        $needJoin = ! empty($filters['npwp']) || ! empty($filters['nip_ar']);
        $query = DB::table('spt_coretax');
        if ($needJoin) {
            $query->leftJoin('masterfile_wp', 'spt_coretax.npwp', '=', 'masterfile_wp.npwp16');
        }
        $query->whereIn(DB::raw('LOWER(spt_coretax.status_spt)'), ['submitted']);
        $query->where(function (Builder $q) {
            $q->where('spt_coretax.pembetulan', '0')->orWhere('spt_coretax.pembetulan', 'Normal')->orWhere('spt_coretax.pembetulan', '0 - Normal')->orWhere('spt_coretax.pembetulan', 'LIKE', '%Normal%');
        });
        if (! empty($filters['npwp'])) {
            $clean = preg_replace('/[^0-9]/', '', $filters['npwp']);
            if ($clean !== '') {
                $this->applyExactNpwpFilter($query, $clean);
            }
        }
        if (! empty($filters['nama'])) {
            $nama = trim($filters['nama']);
            if (mb_strlen($nama) >= 4) {
                $query->whereRaw('MATCH(spt_coretax.nama) AGAINST(? IN BOOLEAN MODE)', ["*{$nama}*"]);
            } else {
                $query->where('spt_coretax.nama', 'LIKE', $nama.'%');
            }
        }
        if (! empty($filters['jenis_spt'])) {
            if ($filters['jenis_spt'] === '__NULL__') {
                $query->where(function (Builder $qq) {
                    $qq->whereNull('spt_coretax.jenis_spt')->orWhere('spt_coretax.jenis_spt', '');
                });
            } else {
                $query->where('spt_coretax.jenis_spt', $filters['jenis_spt']);
            }
        }
        if (! empty($filters['thn_pajak'])) {
            $query->where('spt_coretax.thn_pajak', $filters['thn_pajak']);
        }
        if (! empty($filters['tgl_terima_mulai'])) {
            $query->whereDate('spt_coretax.tgl_terima', '>=', $filters['tgl_terima_mulai']);
        }
        if (! empty($filters['tgl_terima_selesai'])) {
            $query->whereDate('spt_coretax.tgl_terima', '<=', $filters['tgl_terima_selesai']);
        }
        if (! empty($filters['nip_ar'])) {
            $query->where('masterfile_wp.nip_ar', $filters['nip_ar']);
        }

        return $query;
    }

    public function getMatrixKepatuhanData(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $groupQuery = $this->getPivotKepatuhanQuery($filters)->select(['spt_coretax.npwp', DB::raw('MAX(spt_coretax.nama) as nama'), 'spt_coretax.jenis_spt', 'spt_coretax.thn_pajak'])->groupBy('spt_coretax.npwp', 'spt_coretax.jenis_spt', 'spt_coretax.thn_pajak')->orderByRaw('MAX(spt_coretax.nama) ASC');
        $paginated = $groupQuery->paginate($perPage)->withQueryString();
        $npwps = $paginated->pluck('npwp')->unique()->filter()->toArray();
        if (empty($npwps)) {
            return $paginated;
        }
        $details = DB::table('spt_coretax')->whereIn('npwp', $npwps)->whereIn(DB::raw('LOWER(status_spt)'), ['submitted'])->where(function (Builder $q) {
            $q->where('pembetulan', '0')->orWhere('pembetulan', 'Normal')->orWhere('pembetulan', 'LIKE', '%Normal%');
        })
            ->when(! empty($filters['thn_pajak']), fn ($q) => $q->where('thn_pajak', $filters['thn_pajak']))
            ->when(! empty($filters['jenis_spt']), function ($q) use ($filters) {
                if ($filters['jenis_spt'] === '__NULL__') {
                    $q->where(function ($qq) {
                        $qq->whereNull('jenis_spt')->orWhere('jenis_spt', '');
                    });
                } else {
                    $q->where('jenis_spt', $filters['jenis_spt']);
                }
            })
            ->when(! empty($filters['tgl_terima_mulai']), fn ($q) => $q->whereDate('tgl_terima', '>=', $filters['tgl_terima_mulai']))
            ->when(! empty($filters['tgl_terima_selesai']), fn ($q) => $q->whereDate('tgl_terima', '<=', $filters['tgl_terima_selesai']))
            ->select(['npwp', 'jenis_spt', 'thn_pajak', 'masa1_int', 'masa2_int', 'tgl_terima'])->orderBy('npwp')->orderBy('masa1_int')->get();
        $matrix = $paginated->getCollection()->map(function ($group) use ($details) {
            for ($m = 1; $m <= 12; $m++) {
                $group->{'m_'.sprintf('%02d', $m)} = null;
            }
            $gDetails = $details->filter(function ($d) use ($group) {
                $sameNpwp = $d->npwp === $group->npwp;
                $sameJenis = ($d->jenis_spt === $group->jenis_spt) || (is_null($d->jenis_spt) && is_null($group->jenis_spt)) || (trim((string) $d->jenis_spt) === '' && trim((string) $group->jenis_spt) === '');
                $sameThn = $d->thn_pajak == $group->thn_pajak;

                return $sameNpwp && $sameJenis && $sameThn;
            });
            foreach ($gDetails as $d) {
                $m1 = (int) ($d->masa1_int ?? 0);
                $m2 = (int) ($d->masa2_int ?? 0);
                if ($m1 === 0 && $m2 === 0) {
                    continue;
                }if ($m1 === 0) {
                    $m1 = 1;
                }if ($m2 === 0) {
                    $m2 = 12;
                }$m1 = min(12, max(1, $m1));
                $m2 = min(12, max(1, $m2));
                if ($m1 > $m2) {
                    [$m1,$m2] = [$m2, $m1];
                }for ($m = $m1; $m <= $m2; $m++) {
                    $col = 'm_'.sprintf('%02d', $m);
                    if (empty($group->$col) || strtotime($d->tgl_terima) > strtotime($group->$col)) {
                        $group->$col = $d->tgl_terima;
                    }
                }
            }

            return $group;
        });
        $paginated->setCollection($matrix);

        return $paginated;
    }

    public function exportPivotCsv(array $filters): StreamedResponse
    {
        $filename = 'matrix_kepatuhan_'.date('Ymd_His').'.csv';
        $headers = ['NPWP', 'NAMA WP', 'JENIS SPT', 'THN PAJAK', 'MASA 1', 'MASA 2', 'MASA 3', 'MASA 4', 'MASA 5', 'MASA 6', 'MASA 7', 'MASA 8', 'MASA 9', 'MASA 10', 'MASA 11', 'MASA 12'];

        return $this->streamCsvResponse($filename, $headers, function ($file) use ($filters) {
            $q = $this->getPivotKepatuhanQuery($filters)->select(['spt_coretax.npwp', DB::raw('MAX(spt_coretax.nama) as nama'), 'spt_coretax.jenis_spt', 'spt_coretax.thn_pajak'])->groupBy('spt_coretax.npwp', 'spt_coretax.jenis_spt', 'spt_coretax.thn_pajak')->orderByRaw('MAX(spt_coretax.nama) ASC');
            foreach ($q->cursor() as $group) {
                $details = DB::table('spt_coretax')->where('npwp', $group->npwp)->when($group->jenis_spt !== null && trim($group->jenis_spt) !== '', fn ($qq) => $qq->where('jenis_spt', $group->jenis_spt), fn ($qq) => $qq->where(function ($w) {
                    $w->whereNull('jenis_spt')->orWhere('jenis_spt', '');
                }))->where('thn_pajak', $group->thn_pajak)->whereIn(DB::raw('LOWER(status_spt)'), ['submitted'])->where(function ($qq) {
                    $qq->where('pembetulan', '0')->orWhere('pembetulan', 'Normal')->orWhere('pembetulan', 'LIKE', '%Normal%');
                })->select(['masa1_int', 'masa2_int', 'tgl_terima'])->get();
                $rowMasa = array_fill(1, 12, '-');
                foreach ($details as $d) {
                    $m1 = (int) ($d->masa1_int ?? 0);
                    $m2 = (int) ($d->masa2_int ?? 0);
                    if ($m1 === 0 && $m2 === 0) {
                        continue;
                    }if ($m1 === 0) {
                        $m1 = 1;
                    }if ($m2 === 0) {
                        $m2 = 12;
                    }for ($m = $m1; $m <= $m2; $m++) {
                        $val = $d->tgl_terima ? date('Y-m-d', strtotime($d->tgl_terima)) : '-';
                        if ($rowMasa[$m] === '-' || strtotime($val) > strtotime($rowMasa[$m])) {
                            $rowMasa[$m] = $val;
                        }
                    }
                }
                $csv = ["'".$group->npwp, $group->nama ?? '-', trim((string) $group->jenis_spt) === '' ? '(Tanpa Jenis / Kosong)' : $group->jenis_spt, $group->thn_pajak ?? '-'];
                for ($m = 1; $m <= 12; $m++) {
                    $csv[] = $rowMasa[$m];
                } fputcsv($file, $csv);
            }
        });
    }

    /* ================= DROPDOWN - SEKARANG INCLUDE NULL ================= */
    public function getDistinctJenisSpt(): Collection
    {
        // Sekarang include null/kosong juga, sesuai request lu
        // null/kosong akan ditampilkan di filter sebagai (Tanpa Jenis / Kosong) dengan value __NULL__
        return DB::table('spt_coretax')
            ->select('jenis_spt')
            ->distinct()
            ->orderByRaw('jenis_spt IS NULL, jenis_spt ASC')
            ->pluck('jenis_spt');
    }

    public function getDistinctStatusSpt(): Collection
    {
        return DB::table('spt_coretax')->select('status_spt')->whereNotNull('status_spt')->where('status_spt', '!=', '')->distinct()->orderBy('status_spt', 'asc')->pluck('status_spt');
    }

    public function getDistinctPembetulan(): Collection
    {
        return DB::table('spt_coretax')->select('pembetulan')->whereNotNull('pembetulan')->where('pembetulan', '!=', '')->distinct()->orderBy('pembetulan', 'asc')->pluck('pembetulan');
    }

    public function getDistinctAR(?int $tahun = null): Collection
    {
        $tahun = $tahun ?? (int) date('Y');

        return DB::table('pegawai')->select('nip','nama')->where('jabatan','5')->where('tahun',$tahun)->whereNotNull('nip')->where('nip','!=','')->groupBy('nip','nama')->orderBy('nama','asc')->get();
    }
}
