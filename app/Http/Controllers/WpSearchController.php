<?php

namespace App\Http\Controllers;

use App\Models\MasterfileWp;
use App\Models\DetilTransaksiWp;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WpSearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword     = trim($request->get('q'));
        $targetTable = $request->get('target_table', 'masterfile');
        
        // Filter DRM / Transaksi
        $thnSetor = $request->get('thn_setor');
        $blnSetor = $request->get('bln_setor');
        $fungsi   = $request->get('fungsi');
        $nipAr    = $request->get('nip_ar');
        $nipJs    = $request->get('nip_js');

        $sortBy    = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $tahun     = date('Y');

        if ($targetTable === 'masterfile') {
            // ==========================================
            // 1. PENCARIAN MASTERFILE WP (Menggunakan Eloquent Model)
            // ==========================================
            $query = MasterfileWp::query()->with('ar');

            if (!empty($keyword)) {
                // Tentukan apakah pencarian berupa Angka (NPWP) atau Teks (Nama WP)
                if (is_numeric($keyword)) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('npwp15', 'LIKE', "{$keyword}%")
                          ->orWhere('npwp16', 'LIKE', "{$keyword}%");
                    });
                } else {
                    // Jika teks, HANYA gunakan FULLTEXT MATCH AGAINST (Jangan di-OR dengan LIKE NPWP)
                    // Ini cegah Full Table Scan yang bikin timeout!
                    $searchPhrase = '+' . implode(' +', explode(' ', $keyword)) . '*';
                    $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$searchPhrase]);
                }
            }

            // Pengurutan Data
            $allowedSorts = ['npwp15', 'nama', 'alamat', 'jenis'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('nama', 'asc');
            }

            $results = $query->paginate(20)->appends($request->all());

            $listFungsi = [];
            $listAr     = [];
            $listJs     = [];

        } else {
            // ==========================================
            // 2. PENCARIAN DETIL DRM / TRANSAKSI (Late Join / Subquery)
            // ==========================================
            
            // TAHAP 1: Subquery Sangat Ringan untuk mengambil ID saja
            $subQuery = DB::table('detil_transaksi_wp as t_sub');

            if (!empty($keyword)) {
                if (is_numeric($keyword)) {
                    $subQuery->where('t_sub.npwp15', 'LIKE', "{$keyword}%");
                } else {
                    $searchPhrase = '+' . implode(' +', explode(' ', $keyword)) . '*';
                    $subQuery->whereRaw("MATCH(t_sub.nama_wp) AGAINST(? IN BOOLEAN MODE)", [$searchPhrase]);
                }
            }

            // Filter Kriteria
            if ($thnSetor) $subQuery->where('t_sub.thn_setor', $thnSetor);
            if ($blnSetor) $subQuery->where('t_sub.bln_setor', $blnSetor);
            if ($fungsi)   $subQuery->where('t_sub.fungsi', $fungsi);

            // Filter NIP AR / JS
            if ($nipAr || $nipJs) {
                $subQuery->join('masterfile_wp as mf_sub', 't_sub.npwp15', '=', 'mf_sub.npwp15');
                if ($nipAr) $subQuery->where('mf_sub.nip_ar', $nipAr);
                if ($nipJs) $subQuery->where('mf_sub.nip_js', $nipJs);
            }

            // Ordering pada Subquery
            $allowedSorts = ['tgl_setor', 'npwp15', 'fungsi', 'kd_map', 'jml_setor'];
            if (in_array($sortBy, $allowedSorts)) {
                $subQuery->orderBy("t_sub.{$sortBy}", $sortOrder);
            } else {
                $subQuery->orderBy('t_sub.tgl_setor', 'desc');
            }

            // Dapatkan Paginated ID
            $paginatedIds = $subQuery->select('t_sub.id')->paginate(20)->appends($request->all());
            $ids = collect($paginatedIds->items())->pluck('id')->toArray();

            if (!empty($ids)) {
                // TAHAP 2: Ambil Detail Lengkap Hanya untuk 20 ID Terpilih
                $details = DB::table('detil_transaksi_wp as t')
                    ->whereIn('t.id', $ids)
                    ->leftJoin('masterfile_wp as mf', 't.npwp15', '=', 'mf.npwp15')
                    ->leftJoin('kdmap as k', function($join) {
                        $join->on('t.kd_map', '=', 'k.kd_map')
                             ->on('t.kd_bayar', '=', 'k.kd_bayar');
                    })
                    ->leftJoin('pegawai as p_ar', function($join) use ($tahun) {
                        $join->on('mf.nip_ar', '=', 'p_ar.nip')
                             ->where('p_ar.tahun', '=', $tahun);
                    })
                    ->leftJoin('pegawai as p_js', function($join) use ($tahun) {
                        $join->on('mf.nip_js', '=', 'p_js.nip')
                             ->where('p_js.tahun', '=', $tahun);
                    })
                    ->select(
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
                        'p_ar.nama as nama_ar',
                        'p_js.nama as nama_js'
                    );

                if (in_array($sortBy, $allowedSorts)) {
                    $details->orderBy("t.{$sortBy}", $sortOrder);
                } else {
                    $details->orderBy('t.tgl_setor', 'desc');
                }

                $results = $paginatedIds->setCollection($details->get());
            } else {
                $results = $paginatedIds;
            }

            // Dropdown List (Dibuat Statis / Caching Ringan agar Tidak Bikin Timeout)
            $listFungsi = collect(['Penyuluhan', 'Pengawasan', 'Pemeriksaan', 'Penagihan', 'Lainnya']);

            $listAr = Pegawai::where('tahun', $tahun)
                ->select('nip', 'nama')
                ->orderBy('nama', 'asc')
                ->get();

            $listJs = $listAr; // Reuse koleksi pegawai
        }

        return view('search.index', compact(
            'results', 
            'keyword', 
            'targetTable', 
            'thnSetor', 
            'blnSetor', 
            'fungsi', 
            'nipAr', 
            'nipJs', 
            'listFungsi', 
            'listAr', 
            'listJs', 
            'sortBy', 
            'sortOrder'
        ));
    }
}