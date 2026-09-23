<?php

// app/Repositories/WpRepository.php
namespace App\Repositories;

use App\Models\MasterfileWp;
use App\Models\Pegawai;
use Illuminate\Support\Facades\DB;

class WpRepository
{
    /**
     * Pencarian Masterfile WP memanfaatkan Relasi Eloquent (ar)
     */
    public function searchMasterfile(string $keyword, int $limit = 20, int $offset = 0)
    {
        // Gunakan Model Eloquent dengan Eager Loading relasi 'ar'
        $query = MasterfileWp::with(['ar:nip,nama']);

        // Filter berdasarkan input (NPWP / FULLTEXT Nama)
        if (is_numeric($keyword)) {
            $query->where(function ($q) use ($keyword) {
                $q->where('npwp15', 'LIKE', $keyword . '%')
                  ->orWhere('npwp16', 'LIKE', $keyword . '%');
            });
        } else {
            $searchTerm = '+' . implode('* +', explode(' ', trim($keyword))) . '*';
            $query->where(function ($q) use ($searchTerm, $keyword) {
                $q->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$searchTerm])
                  ->orWhere('npwp15', 'LIKE', $keyword . '%');
            });
        }

        return $query->orderBy('nama', 'asc')
            ->limit($limit)
            ->offset($offset)
            ->get()
            ->map(function ($wp) {
                // Transformasi output agar sesuai format DTO / Response API
                return [
                    'npwp15'    => $wp->npwp15,
                    'npwp16'    => $wp->npwp16,
                    'nama_wp'   => $wp->nama,
                    'alamat'    => $wp->alamat,
                    'kelurahan' => $wp->kelurahan,
                    'kecamatan' => $wp->kecamatan,
                    'kota'      => $wp->kota,
                    'status_wp' => $wp->status,
                    'jenis_wp'  => $wp->jenis,
                    'telp'      => $wp->telp,
                    'nama_ar'   => $wp->ar->nama ?? null, // Diambil via relasi $wp->ar
                ];
            });
    }

    /**
     * Pencarian Detil Transaksi WP 
     * (Tetap menggunakan Late Join Query Builder untuk performa query ribuan data transaksi)
     */
    public function searchTransactions(array $filters, int $limit = 20, int $offset = 0)
    {
        $keyword = $filters['keyword'] ?? null;

        // Step 1: Subquery Late Join ID Transaksi
        $subQuery = DB::table('detil_transaksi_wp as t_sub')
            ->select('t_sub.id');

        if ($keyword) {
            if (is_numeric($keyword)) {
                $subQuery->where('t_sub.npwp15', 'LIKE', $keyword . '%');
            } else {
                $searchTerm = '+' . implode('* +', explode(' ', trim($keyword))) . '*';
                $subQuery->leftJoin('masterfile_wp as mf_sub', 't_sub.npwp15', '=', 'mf_sub.npwp15')
                    ->where(function ($q) use ($searchTerm) {
                        $q->whereRaw("MATCH(t_sub.nama_wp) AGAINST(? IN BOOLEAN MODE)", [$searchTerm])
                          ->orWhereRaw("MATCH(mf_sub.nama) AGAINST(? IN BOOLEAN MODE)", [$searchTerm]);
                    });
            }
        }

        if (!empty($filters['thn_setor'])) {
            $subQuery->where('t_sub.thn_setor', $filters['thn_setor']);
        }
        if (!empty($filters['bln_setor'])) {
            $subQuery->where('t_sub.bln_setor', $filters['bln_setor']);
        }
        if (!empty($filters['fungsi'])) {
            $subQuery->where('t_sub.fungsi', $filters['fungsi']);
        }

        $subQuery->orderBy('t_sub.tgl_setor', 'desc')
            ->limit($limit)
            ->offset($offset);

        // Step 2: Join Hasil
        return DB::table('detil_transaksi_wp as t')
            ->joinSub($subQuery, 'matched', function ($join) {
                $join->on('t.id', '=', 'matched.id');
            })
            ->leftJoin('masterfile_wp as mf', 't.npwp15', '=', 'mf.npwp15')
            ->leftJoin('kdmap as k', function ($join) {
                $join->on('t.kd_map', '=', 'k.kd_map')
                     ->on('t.kd_bayar', '=', 'k.kd_bayar');
            })
            ->leftJoin('pegawai as p_ar', function ($join) {
                $join->on('mf.nip_ar', '=', 'p_ar.nip')
                     ->where('p_ar.tahun', '=', date('Y'));
            })
            ->leftJoin('pegawai as p_js', function ($join) {
                $join->on('mf.nip_js', '=', 'p_js.nip')
                     ->where('p_js.tahun', '=', date('Y'));
            })
            ->select([
                't.id', 't.npwp15', 't.nama_wp', 't.ntpn', 't.tgl_setor',
                't.thn_pajak', 't.masa_pajak', 't.jml_setor', 't.kd_map', 't.kd_bayar',
                't.fungsi', 't.jenis as jenis_transaksi', 'k.jenis_pajak',
                'mf.nama as nama_master', 'p_ar.nama as nama_ar', 'p_js.nama as nama_js'
            ])
            ->orderBy('t.tgl_setor', 'desc')
            ->get();
    }

    /**
     * Data Dropdown Filter
     */
    public function getDropdownOptions(): array
    {
        $tahun = date('Y');

        $fungsiList = DB::table('detil_transaksi_wp')
            ->whereNotNull('fungsi')
            ->where('fungsi', '!=', '')
            ->distinct()
            ->pluck('fungsi');

        $arList = Pegawai::where('tahun', $tahun)
            ->whereIn('nip', function ($q) {
                $q->select('nip_ar')->from('masterfile_wp')->whereNotNull('nip_ar')->distinct();
            })
            ->select('nip', 'nama')
            ->orderBy('nama')
            ->get();

        $jsList = Pegawai::where('tahun', $tahun)
            ->whereIn('nip', function ($q) {
                $q->select('nip_js')->from('masterfile_wp')->whereNotNull('nip_js')->distinct();
            })
            ->select('nip', 'nama')
            ->orderBy('nama')
            ->get();

        return [
            'fungsi' => $fungsiList,
            'ar'     => $arList,
            'js'     => $jsList,
        ];
    }
}