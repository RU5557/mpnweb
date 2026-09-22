<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjagaanController extends Controller
{
    private function getFungsiOptions()
    {
        return Cache::remember('penjagaan_fungsi_options', 86400, function () {
            return DB::table('detil_transaksi_wp')
                ->select('fungsi')
                ->whereNotNull('fungsi')
                ->distinct()
                ->orderBy('fungsi')
                ->pluck('fungsi');
        });
    }

    // 1. Penjagaan Bulanan
    public function bulanan(Request $request)
    {
        $fungsiOptions = $this->getFungsiOptions();
        $fungsi = $request->has('fungsi') ? (array) $request->input('fungsi', []) : $fungsiOptions->toArray();

        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $fungsiKey = implode(',', $fungsi);
        $cacheKey = 'penjagaan_bulanan_' . md5("y:{$tahunIni}_f:{$fungsiKey}");

        $data = Cache::remember($cacheKey, 3600, function () use ($fungsi, $tahunIni, $tahunLalu) {
            $queryTahunLalu = DB::table('detil_transaksi_wp')
                ->select(DB::raw('bln_setor, SUM(jml_setor) as total'))
                ->where('thn_setor', $tahunLalu);

            $queryTahunIni = DB::table('detil_transaksi_wp')
                ->select(DB::raw('bln_setor, SUM(jml_setor) as total'))
                ->where('thn_setor', $tahunIni);

            if (!empty($fungsi)) {
                $queryTahunLalu->whereIn('fungsi', $fungsi);
                $queryTahunIni->whereIn('fungsi', $fungsi);
            }

            return [
                'lalu' => $queryTahunLalu->groupBy('bln_setor')->pluck('total', 'bln_setor')->toArray(),
                'ini'  => $queryTahunIni->groupBy('bln_setor')->pluck('total', 'bln_setor')->toArray(),
            ];
        });

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data2025 = [];
        $data2026 = [];

        for ($m = 1; $m <= 12; $m++) {
            $data2025[] = (float) ($data['lalu'][$m] ?? 0);
            $data2026[] = (float) ($data['ini'][$m] ?? 0);
        }

        return view('penerimaan.penjagaan.bulanan', compact(
            'months', 'data2025', 'data2026', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunLalu'
        ));
    }

    // 2. Penjagaan Harian
    public function harian(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsiOptions = $this->getFungsiOptions();
        $fungsi = $request->has('fungsi') ? (array) $request->input('fungsi', []) : $fungsiOptions->toArray();

        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $fungsiKey = implode(',', $fungsi);
        $cacheKey = 'penjagaan_harian_' . md5("y:{$tahunIni}_b:{$bulan}_f:{$fungsiKey}");

        $data = Cache::remember($cacheKey, 3600, function () use ($bulan, $fungsi, $tahunIni, $tahunLalu) {
            $queryTahunLalu = DB::table('detil_transaksi_wp')
                ->select(DB::raw('DAY(tgl_setor) as tgl, SUM(jml_setor) as total'))
                ->where('thn_setor', $tahunLalu)
                ->where('bln_setor', $bulan);

            $queryTahunIni = DB::table('detil_transaksi_wp')
                ->select(DB::raw('DAY(tgl_setor) as tgl, SUM(jml_setor) as total'))
                ->where('thn_setor', $tahunIni)
                ->where('bln_setor', $bulan);

            if (!empty($fungsi)) {
                $queryTahunLalu->whereIn('fungsi', $fungsi);
                $queryTahunIni->whereIn('fungsi', $fungsi);
            }

            return [
                'lalu' => $queryTahunLalu->groupBy('tgl')->pluck('total', 'tgl')->toArray(),
                'ini'  => $queryTahunIni->groupBy('tgl')->pluck('total', 'tgl')->toArray(),
            ];
        });

        $days = range(1, 31);
        $data2025 = [];
        $data2026 = [];

        foreach ($days as $day) {
            $data2025[] = (float) ($data['lalu'][$day] ?? 0);
            $data2026[] = (float) ($data['ini'][$day] ?? 0);
        }

        return view('penerimaan.penjagaan.harian', compact(
            'days', 'data2025', 'data2026', 'bulan', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunLalu'
        ));
    }

    // 3. Penjagaan vs Bulan Lalu
    public function vsBulanLalu(Request $request)
    {
        $fungsiOptions = $this->getFungsiOptions();
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsi = $request->has('fungsi') ? (array) $request->input('fungsi', []) : $fungsiOptions->toArray();

        $tahunIni = (int) date('Y');
        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunBulanLalu = $bulan == 1 ? $tahunIni - 1 : $tahunIni;

        $fungsiKey = implode(',', $fungsi);
        $cacheKey = 'penjagaan_vs_bulan_lalu_' . md5("y:{$tahunIni}_b:{$bulan}_f:{$fungsiKey}");

        $data = Cache::remember($cacheKey, 3600, function () use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu, $fungsi) {
            $queryBulanIni = DB::table('detil_transaksi_wp')
                ->select(DB::raw('DAY(tgl_setor) as tgl, SUM(jml_setor) as total'))
                ->where('thn_setor', $tahunIni)
                ->where('bln_setor', $bulan);

            $queryBulanLalu = DB::table('detil_transaksi_wp')
                ->select(DB::raw('DAY(tgl_setor) as tgl, SUM(jml_setor) as total'))
                ->where('thn_setor', $tahunBulanLalu)
                ->where('bln_setor', $bulanLalu);

            if (!empty($fungsi)) {
                $queryBulanIni->whereIn('fungsi', $fungsi);
                $queryBulanLalu->whereIn('fungsi', $fungsi);
            }

            return [
                'ini'  => $queryBulanIni->groupBy('tgl')->pluck('total', 'tgl')->toArray(),
                'lalu' => $queryBulanLalu->groupBy('tgl')->pluck('total', 'tgl')->toArray(),
            ];
        });

        $days = range(1, 31);
        $dataBulanIni = [];
        $dataBulanLalu = [];

        foreach ($days as $day) {
            $dataBulanIni[] = (float) ($data['ini'][$day] ?? 0);
            $dataBulanLalu[] = (float) ($data['lalu'][$day] ?? 0);
        }

        return view('penerimaan.penjagaan.vs_bulan_lalu', compact(
            'days', 'dataBulanIni', 'dataBulanLalu', 'bulan', 'bulanLalu', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunBulanLalu'
        ));
    }

    public function exportBulananCsv(Request $request): StreamedResponse
    {
        $fungsiOptions = $this->getFungsiOptions();
        $fungsi = $request->has('fungsi') ? (array) $request->input('fungsi', []) : $fungsiOptions->toArray();
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $fileName = 'penjagaan_bulanan_detil_' . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Tahun Setor', 'Bulan Setor', 'Tanggal Setor', 'NPWP15', 
            'Nama WP', 'Jenis', 'Fungsi', 'Kode MAP', 'Kode Bayar', 
            'Masa Pajak', 'Tahun Pajak', 'Jumlah Setor (Rp)', 'NTPN'
        ];

        $callback = function () use ($fungsi, $columns, $tahunIni, $tahunLalu) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 
            fputcsv($file, $columns);

            $query = DB::table('detil_transaksi_wp')
                ->select([
                    'thn_setor', 'bln_setor', 'tgl_setor', 'npwp15', 
                    'nama_wp', 'jenis', 'fungsi', 'kd_map', 'kd_bayar', 
                    'masa_pajak', 'thn_pajak', 'jml_setor', 'ntpn'
                ])
                ->whereIn('thn_setor', [$tahunLalu, $tahunIni]);

            if (!empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            $query->orderBy('thn_setor', 'desc')
                ->orderBy('bln_setor', 'desc')
                ->cursor()
                ->each(function ($row) use ($file) {
                    fputcsv($file, (array) $row);
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportHarianCsv(Request $request): StreamedResponse
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsiOptions = $this->getFungsiOptions();
        $fungsi = $request->has('fungsi') ? (array) $request->input('fungsi', []) : $fungsiOptions->toArray();
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $fileName = 'penjagaan_harian_detil_bln_' . $bulan . '_' . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Tahun Setor', 'Bulan Setor', 'Tanggal Setor', 'NPWP15', 
            'Nama WP', 'Jenis', 'Fungsi', 'Kode MAP', 'Kode Bayar', 
            'Masa Pajak', 'Tahun Pajak', 'Jumlah Setor (Rp)', 'NTPN'
        ];

        $callback = function () use ($bulan, $fungsi, $columns, $tahunIni, $tahunLalu) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 
            fputcsv($file, $columns);

            $query = DB::table('detil_transaksi_wp')
                ->select([
                    'thn_setor', 'bln_setor', 'tgl_setor', 'npwp15', 
                    'nama_wp', 'jenis', 'fungsi', 'kd_map', 'kd_bayar', 
                    'masa_pajak', 'thn_pajak', 'jml_setor', 'ntpn'
                ])
                ->whereIn('thn_setor', [$tahunLalu, $tahunIni])
                ->where('bln_setor', $bulan);

            if (!empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            $query->orderBy('tgl_setor', 'desc')
                ->cursor()
                ->each(function ($row) use ($file) {
                    fputcsv($file, (array) $row);
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportVsBulanLaluCsv(Request $request): StreamedResponse
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsiOptions = $this->getFungsiOptions();
        $fungsi = $request->has('fungsi') ? (array) $request->input('fungsi', []) : $fungsiOptions->toArray();

        $tahunIni = (int) date('Y');
        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunBulanLalu = $bulan == 1 ? $tahunIni - 1 : $tahunIni;

        $fileName = 'penjagaan_vs_bulan_lalu_bln_' . $bulan . '_' . date('Ymd_His') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Tahun Setor', 'Bulan Setor', 'Tanggal Setor', 'NPWP15', 
            'Nama WP', 'Jenis', 'Fungsi', 'Kode MAP', 'Kode Bayar', 
            'Masa Pajak', 'Tahun Pajak', 'Jumlah Setor (Rp)', 'NTPN'
        ];

        $callback = function () use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu, $fungsi, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 
            fputcsv($file, $columns);

            $query = DB::table('detil_transaksi_wp')
                ->select([
                    'thn_setor', 'bln_setor', 'tgl_setor', 'npwp15', 
                    'nama_wp', 'jenis', 'fungsi', 'kd_map', 'kd_bayar', 
                    'masa_pajak', 'thn_pajak', 'jml_setor', 'ntpn'
                ])
                ->where(function ($q) use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu) {
                    $q->where(function ($q1) use ($bulan, $tahunIni) {
                        $q1->where('thn_setor', $tahunIni)->where('bln_setor', $bulan);
                    })->orWhere(function ($q2) use ($bulanLalu, $tahunBulanLalu) {
                        $q2->where('thn_setor', $tahunBulanLalu)->where('bln_setor', $bulanLalu);
                    });
                });

            if (!empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            $query->orderBy('tgl_setor', 'desc')
                ->cursor()
                ->each(function ($row) use ($file) {
                    fputcsv($file, (array) $row);
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}