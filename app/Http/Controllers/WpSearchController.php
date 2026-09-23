<?php

namespace App\Http\Controllers;

use App\Models\MasterfileWp;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class WpSearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword     = trim($request->get('q'));
        $targetTable = $request->get('target_table', 'masterfile');

        $sortBy    = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $tahun     = date('Y');

        // ==========================================
        // AMBIL DATA LIST UNTUK FILTER (CACHED)
        // ==========================================
        $listTahunSetor = Cache::remember('filter_thn_setor', 3600, function () {
            return DB::table('detil_transaksi_wp')
                ->whereNotNull('thn_setor')
                ->where('thn_setor', '!=', '')
                ->distinct()
                ->orderBy('thn_setor', 'desc')
                ->pluck('thn_setor')
                ->toArray();
        });

        $listFungsi = Cache::remember('filter_fungsi_list', 3600, function () {
            return DB::table('detil_transaksi_wp')
                ->whereNotNull('fungsi')
                ->where('fungsi', '!=', '')
                ->distinct()
                ->orderBy('fungsi', 'asc')
                ->pluck('fungsi')
                ->toArray();
        });

        $listAr = Cache::remember('filter_ar_jabatan_5_' . $tahun, 3600, function () use ($tahun) {
            return Pegawai::where('jabatan', 5)
                ->where('tahun', $tahun)
                ->select('nip', 'nama')
                ->orderBy('nama', 'asc')
                ->get();
        });

        $listJs = Cache::remember('filter_js_jabatan_11_' . $tahun, 3600, function () use ($tahun) {
            return Pegawai::where('jabatan', 11)
                ->where('tahun', $tahun)
                ->select('nip', 'nama')
                ->orderBy('nama', 'asc')
                ->get();
        });

        // ==========================================
        // DEFAULT TERPILIH SEMUA KETIKA AWAL DIBUKA
        // ==========================================
        $allBulan = ['01','02','03','04','05','06','07','08','09','10','11','12'];
        $allTahun = collect($listTahunSetor)->toArray();
        $allFungsi = collect($listFungsi)->toArray();
        $allAr    = collect($listAr)->pluck('nip')->toArray();
        $allJs    = collect($listJs)->pluck('nip')->toArray();

        if ($request->has('has_search')) {
            $thnSetor = (array) $request->get('thn_setor', []);
            $blnSetor = (array) $request->get('bln_setor', []);
            $fungsi   = (array) $request->get('fungsi', []);
            $nipAr    = (array) $request->get('nip_ar', []);
            $nipJs    = (array) $request->get('nip_js', []);
        } else {
            // Default pertama kali buka: Pilihlah SEMUA
            $thnSetor = $allTahun;
            $blnSetor = $allBulan;
            $fungsi   = $allFungsi;
            $nipAr    = $allAr;
            $nipJs    = $allJs;
        }

        // ==========================================
        // EXECUTE QUERY
        // ==========================================
        if ($targetTable === 'masterfile') {
            
            $query = MasterfileWp::query()->with(['ar', 'js']);

            if (!empty($keyword)) {
                if (is_numeric($keyword)) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('npwp15', 'LIKE', "{$keyword}%")
                          ->orWhere('npwp16', 'LIKE', "{$keyword}%");
                    });
                } else {
                    $searchPhrase = '+' . implode(' +', explode(' ', $keyword)) . '*';
                    $query->whereRaw("MATCH(nama) AGAINST(? IN BOOLEAN MODE)", [$searchPhrase]);
                }
            }

            $allowedSorts = ['npwp15', 'nama', 'alamat', 'jenis'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('nama', 'asc');
            }

            $results = $query->paginate(20)->appends($request->all());

        } else {
            
            $subQuery = DB::table('detil_transaksi_wp as t_sub');

            if (!empty($keyword)) {
                if (is_numeric($keyword)) {
                    $subQuery->where('t_sub.npwp15', 'LIKE', "{$keyword}%");
                } else {
                    $searchPhrase = '+' . implode(' +', explode(' ', $keyword)) . '*';
                    $subQuery->whereRaw("MATCH(t_sub.nama_wp) AGAINST(? IN BOOLEAN MODE)", [$searchPhrase]);
                }
            }

            // --- Filter Tahun Setor ---
            if (count($thnSetor) > 0 && count($thnSetor) < count($allTahun)) {
                $subQuery->whereIn('t_sub.thn_setor', $thnSetor);
            } elseif (count($thnSetor) === 0) {
                $subQuery->whereRaw('1 = 0');
            }
            
            // --- Filter Bulan Setor ---
            if (count($blnSetor) > 0 && count($blnSetor) < count($allBulan)) {
                $subQuery->whereIn('t_sub.bln_setor', $blnSetor);
            } elseif (count($blnSetor) === 0) {
                $subQuery->whereRaw('1 = 0');
            }

            // --- Filter Fungsi ---
            if (count($fungsi) > 0 && count($fungsi) < count($allFungsi)) {
                $subQuery->whereIn('t_sub.fungsi', $fungsi);
            } elseif (count($fungsi) === 0) {
                $subQuery->whereRaw('1 = 0');
            }

            // --- Filter Multiple AR & JS ---
            $isArFiltered = count($nipAr) > 0 && count($nipAr) < count($allAr);
            $isJsFiltered = count($nipJs) > 0 && count($nipJs) < count($allJs);

            if ($isArFiltered || $isJsFiltered || count($nipAr) === 0 || count($nipJs) === 0) {
                $subQuery->join('masterfile_wp as mf_sub', 't_sub.npwp15', '=', 'mf_sub.npwp15');
                
                if (count($nipAr) === 0 || count($nipJs) === 0) {
                    $subQuery->whereRaw('1 = 0');
                } else {
                    if ($isArFiltered) {
                        $subQuery->whereIn('mf_sub.nip_ar', $nipAr);
                    }
                    if ($isJsFiltered) {
                        $subQuery->whereIn('mf_sub.nip_js', $nipJs);
                    }
                }
            }

            // Ordering
            $allowedSorts = ['tgl_setor', 'npwp15', 'fungsi', 'kd_map', 'jml_setor'];
            if (in_array($sortBy, $allowedSorts)) {
                $subQuery->orderBy("t_sub.{$sortBy}", $sortOrder);
            } else {
                $subQuery->orderBy('t_sub.tgl_setor', 'desc');
            }

            $paginatedIds = $subQuery->select('t_sub.id')->paginate(20)->appends($request->all());
            $ids = collect($paginatedIds->items())->pluck('id')->toArray();

            if (!empty($ids)) {
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
                        't.id', 't.npwp15', 't.nama_wp', 't.ntpn', 't.tgl_setor',
                        't.thn_pajak', 't.masa_pajak', 't.jml_setor', 't.kd_map',
                        't.kd_bayar', 't.fungsi', 't.jenis as jenis_transaksi',
                        'k.jenis_pajak', 'mf.nama as nama_master',
                        'p_ar.nama as nama_ar', 'p_js.nama as nama_js'
                    )
                    ->orderByRaw("FIELD(t.id, " . implode(',', $ids) . ")");

                $results = $paginatedIds->setCollection($details->get());
            } else {
                $results = $paginatedIds;
            }
        }

        return view('search.index', compact(
            'results', 'keyword', 'targetTable', 'thnSetor', 
            'blnSetor', 'fungsi', 'nipAr', 'nipJs', 
            'listTahunSetor', 'listFungsi', 'listAr', 'listJs', 
            'sortBy', 'sortOrder'
        ));
    }
}