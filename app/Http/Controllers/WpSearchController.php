<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WpSearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = trim($request->get('q'));
        $targetTable = $request->get('target_table', 'masterfile');
        $thnSetor = $request->get('thn_setor');
        $blnSetor = $request->get('bln_setor');
        $sortBy = $request->get('sort_by');
        $sortOrder = $request->get('sort_order', 'asc') === 'desc' ? 'desc' : 'asc';
        $tahun = date('Y');

        if (!$keyword && !$thnSetor && !$blnSetor) {
            return view('search.index', [
                'results'     => null, 
                'keyword'     => '', 
                'targetTable' => $targetTable,
                'thnSetor'    => $thnSetor,
                'blnSetor'    => $blnSetor,
                'sortBy'      => $sortBy,
                'sortOrder'   => $sortOrder,
            ]);
        }

        if ($targetTable === 'masterfile') {
            // --- PENCARIAN DI TABEL MASTERFILE_WP ---
            $query = DB::table('masterfile_wp as mf')
                ->leftJoin('pegawai as p', function($join) use ($tahun) {
                    $join->on('mf.nip_ar', '=', 'p.nip')
                         ->where('p.tahun', '=', $tahun);
                });

            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('mf.npwp15', 'LIKE', "%{$keyword}%")
                      ->orWhere('mf.nama', 'LIKE', "%{$keyword}%")
                      ->orWhere('mf.nik', 'LIKE', "%{$keyword}%")
                      ->orWhere('mf.npwp16', 'LIKE', "%{$keyword}%");
                });
            }

            // Allowed Columns untuk Sorting Masterfile
            $allowedSorts = ['npwp15', 'nama_wp', 'alamat', 'jenis_wp', 'nama_ar'];
            if (in_array($sortBy, $allowedSorts)) {
                $column = $sortBy === 'nama_wp' ? 'mf.nama' : ($sortBy === 'nama_ar' ? 'p.nama' : "mf.{$sortBy}");
                $query->orderBy($column, $sortOrder);
            } else {
                $query->orderBy('mf.nama', 'asc'); // Default Sort
            }

            $results = $query->select(
                    'mf.npwp15',
                    'mf.npwp16',
                    'mf.nama as nama_wp',
                    'mf.alamat',
                    'mf.kelurahan',
                    'mf.kecamatan',
                    'mf.kota',
                    'mf.status as status_wp',
                    'mf.jenis as jenis_wp',
                    'mf.telp',
                    'p.nama as nama_ar'
                )
                ->paginate(20)
                ->appends([
                    'q' => $keyword, 
                    'target_table' => $targetTable,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]);

        } else {
            // --- PENCARIAN DI TABEL DETIL_TRANSAKSI_WP ---
            $query = DB::table('detil_transaksi_wp as t')
                ->leftJoin('masterfile_wp as mf', 't.npwp15', '=', 'mf.npwp15')
                ->leftJoin('kdmap as k', function($join) {
                    $join->on('t.kd_map', '=', 'k.kd_map')
                         ->on('t.kd_bayar', '=', 'k.kd_bayar');
                })
                ->leftJoin('pegawai as p', function($join) use ($tahun) {
                    $join->on('mf.nip_ar', '=', 'p.nip')
                         ->where('p.tahun', '=', $tahun);
                });

            // Filter Keyword (Mengganti NTPN dengan Fungsi)
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('t.npwp15', 'LIKE', "%{$keyword}%")
                      ->orWhere('t.nama_wp', 'LIKE', "%{$keyword}%")
                      ->orWhere('t.fungsi', 'LIKE', "%{$keyword}%")
                      ->orWhere('mf.nama', 'LIKE', "%{$keyword}%");
                });
            }

            // Filter Tambahan Tahun & Bulan Setor
            if ($thnSetor) {
                $query->where('t.thn_setor', '=', $thnSetor);
            }
            if ($blnSetor) {
                $query->where('t.bln_setor', '=', $blnSetor);
            }

            // Allowed Columns untuk Sorting Transaksi
            $allowedSorts = ['tgl_setor', 'npwp15', 'fungsi', 'kd_map', 'jml_setor', 'nama_ar'];
            if (in_array($sortBy, $allowedSorts)) {
                $column = $sortBy === 'nama_ar' ? 'p.nama' : "t.{$sortBy}";
                $query->orderBy($column, $sortOrder);
            } else {
                $query->orderBy('t.tgl_setor', 'desc'); // Default Sort
            }

            $results = $query->select(
                    't.id',
                    't.npwp15',
                    't.nama_wp',
                    't.ntpn',
                    't.tgl_setor',
                    't.thn_pajak',
                    't.masa_pajak',
                    't.jml_setor',
                    't.kd_map',
                    't.kd_bayar',
                    't.fungsi',
                    't.jenis as jenis_transaksi',
                    'k.jenis_pajak',
                    'mf.nama as nama_master',
                    'p.nama as nama_ar'
                )
                ->paginate(20)
                ->appends([
                    'q' => $keyword, 
                    'target_table' => $targetTable,
                    'thn_setor' => $thnSetor,
                    'bln_setor' => $blnSetor,
                    'sort_by' => $sortBy,
                    'sort_order' => $sortOrder
                ]);
        }

        return view('search.index', compact('results', 'keyword', 'targetTable', 'thnSetor', 'blnSetor', 'sortBy', 'sortOrder'));
    }
}