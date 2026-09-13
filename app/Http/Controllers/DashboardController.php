<?php

namespace App\Http\Controllers;

use App\Models\SummaryPenerimaanKpp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));

        // Query super ringan ke tabel summary
        $penerimaanBulanan = SummaryPenerimaanKpp::select(
                'bln_setor', 
                DB::raw('SUM(total_setor) as total_penerimaan'),
                DB::raw('SUM(total_transaksi) as total_tx')
            )
            ->where('thn_setor', $tahun)
            ->groupBy('bln_setor')
            ->orderBy('bln_setor', 'asc')
            ->get();

        $totalTahunIni = $penerimaanBulanan->sum('total_penerimaan');

        return view('dashboard.index', compact('penerimaanBulanan', 'totalTahunIni', 'tahun'));
    }
}