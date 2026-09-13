<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WpSearchController extends Controller
{
    public function search(Request $request)
    {
        $keyword = trim($request->get('q'));

        if (!$keyword) {
            return view('search.index', ['results' => null]);
        }

        $tahun = date('Y');

        $results = DB::table('detil_transaksi_wp as t')
            ->leftJoin('masterfile_wp as mf', 't.npwp15', '=', 'mf.npwp15')
            ->leftJoin('kdmap as k', function($join) {
                $join->on('t.kd_map', '=', 'k.kd_map')
                     ->on('t.kd_bayar', '=', 'k.kd_bayar');
            })
            ->leftJoin('pegawai as p', function($join) use ($tahun) {
                $join->on('mf.nip_ar', '=', 'p.nip')
                     ->where('p.tahun', '=', $tahun);
            })
            ->where('t.npwp15', $keyword)
            ->orWhere('t.nama_wp', 'LIKE', "%{$keyword}%")
            ->orWhere('mf.nama', 'LIKE', "%{$keyword}%")
            ->select(
                't.id',
                't.npwp15',
                't.nama_wp',
                't.tgl_setor',
                't.jml_setor',
                't.kd_map',
                't.kd_bayar',
                'k.jenis_pajak',
                'mf.nama as nama_master',
                'mf.status as status_wp',
                'p.nama as nama_ar'
            )
            ->paginate(20);

        return view('search.index', compact('results', 'keyword'));
    }
}