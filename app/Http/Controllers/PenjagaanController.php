<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjagaanController extends Controller
{
    /**
     * Cache opsi filter 'fungsi' selama 24 jam
     */
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

    // 1. Penjagaan Bulanan (2026 vs 2025 per Bulan)
    public function bulanan(Request $request)
    {
        $fungsiOptions = $this->getFungsiOptions();

        // Default: Jika request tidak membawa parameter (akses pertama kali), 
        // centang/pilih SEMUA opsi yang tersedia.
        if (!$request->has('fungsi')) {
            $fungsi = $fungsiOptions->toArray();
        } else {
            $fungsi = (array) $request->input('fungsi', []);
        }

        // Buat cache key berdasarkan filter
        $fungsiKey = implode(',', $fungsi);
        $cacheKey = 'penjagaan_bulanan_' . md5("f:{$fungsiKey}");

        $data = Cache::remember($cacheKey, 3600, function () use ($fungsi) {
            $query2025 = DB::table('detil_transaksi_wp')
                ->select(DB::raw('bln_setor, SUM(jml_setor) as total'))
                ->where('thn_setor', 2025);

            $query2026 = DB::table('detil_transaksi_wp')
                ->select(DB::raw('bln_setor, SUM(jml_setor) as total'))
                ->where('thn_setor', 2026);

            if (!empty($fungsi)) {
                $query2025->whereIn('fungsi', $fungsi);
                $query2026->whereIn('fungsi', $fungsi);
            }

            return [
                '2025' => $query2025->groupBy('bln_setor')->pluck('total', 'bln_setor')->toArray(),
                '2026' => $query2026->groupBy('bln_setor')->pluck('total', 'bln_setor')->toArray(),
            ];
        });

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $data2025 = [];
        $data2026 = [];

        for ($m = 1; $m <= 12; $m++) {
            $data2025[] = (float) ($data['2025'][$m] ?? 0);
            $data2026[] = (float) ($data['2026'][$m] ?? 0);
        }

        return view('penerimaan.penjagaan.bulanan', compact(
            'months', 'data2025', 'data2026', 'fungsiOptions', 'fungsi'
        ));
    }

    // 2. Penjagaan Harian (2026 vs 2025 pada Bulan yang Sama)
    public function harian(Request $request)
    {
        $fungsi = (array) $request->input('fungsi', []);
        $bulan = (int) $request->input('bulan', date('m'));
        
        $fungsiOptions = $this->getFungsiOptions();

        $fungsiKey = implode(',', $fungsi);
        $cacheKey = 'penjagaan_harian_' . md5("b:{$bulan}_f:{$fungsiKey}");

        $data = Cache::remember($cacheKey, 3600, function () use ($bulan, $fungsi) {
            $query2025 = DB::table('detil_transaksi_wp')
                ->select(DB::raw('DAY(tgl_setor) as tgl, SUM(jml_setor) as total'))
                ->where('thn_setor', 2025)
                ->where('bln_setor', $bulan);

            $query2026 = DB::table('detil_transaksi_wp')
                ->select(DB::raw('DAY(tgl_setor) as tgl, SUM(jml_setor) as total'))
                ->where('thn_setor', 2026)
                ->where('bln_setor', $bulan);

            if (!empty($fungsi)) {
                $query2025->whereIn('fungsi', $fungsi);
                $query2026->whereIn('fungsi', $fungsi);
            }

            return [
                '2025' => $query2025->groupBy('tgl')->pluck('total', 'tgl')->toArray(),
                '2026' => $query2026->groupBy('tgl')->pluck('total', 'tgl')->toArray(),
            ];
        });

        $days = range(1, 31);
        $data2025 = [];
        $data2026 = [];

        foreach ($days as $day) {
            $data2025[] = (float) ($data['2025'][$day] ?? 0);
            $data2026[] = (float) ($data['2026'][$day] ?? 0);
        }

        return view('penerimaan.penjagaan.harian', compact('days', 'data2025', 'data2026', 'bulan', 'fungsiOptions', 'fungsi'));
    }

    // 3. Penjagaan vs Bulan Lalu (2026 Bulan Ini vs Bulan Sebelumnya)
    public function vsBulanLalu(Request $request)
    {
        $fungsi = (array) $request->input('fungsi', []);
        $bulan = (int) $request->input('bulan', date('m'));
        
        $fungsiOptions = $this->getFungsiOptions();

        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunBulanLalu = $bulan == 1 ? 2025 : 2026;

        $fungsiKey = implode(',', $fungsi);
        $cacheKey = 'penjagaan_vs_bulan_lalu_' . md5("b:{$bulan}_f:{$fungsiKey}");

        $data = Cache::remember($cacheKey, 3600, function () use ($bulan, $bulanLalu, $tahunBulanLalu, $fungsi) {
            $queryBulanIni = DB::table('detil_transaksi_wp')
                ->select(DB::raw('DAY(tgl_setor) as tgl, SUM(jml_setor) as total'))
                ->where('thn_setor', 2026)
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

        return view('penerimaan.penjagaan.vs_bulan_lalu', compact('days', 'dataBulanIni', 'dataBulanLalu', 'bulan', 'bulanLalu', 'fungsiOptions', 'fungsi'));
    }

    public function exportBulananCsv(Request $request): StreamedResponse
    {
        $fungsiOptions = $this->getFungsiOptions();

        // Tangkap filter sesuai pilihan di UI
            if (!$request->has('fungsi')) {
            $fungsi = $fungsiOptions->toArray();
        } else {
            $fungsi = (array) $request->input('fungsi', []);
        }

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

        $callback = function () use ($fungsi, $columns) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM untuk Microsoft Excel
            fputs($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); 
            fputcsv($file, $columns);

            // Query data detil transaksi (2025 vs 2026)
            $query = DB::table('detil_transaksi_wp')
                ->select([
                    'thn_setor', 'bln_setor', 'tgl_setor', 'npwp15', 
                    'nama_wp', 'jenis', 'fungsi', 'kd_map', 'kd_bayar', 
                    'masa_pajak', 'thn_pajak', 'jml_setor', 'ntpn'
                ])
                ->whereIn('thn_setor', [2025, 2026]);

            if (!empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            // Gunakan cursor() agar efisien dan hemat memori RAM
            $query->orderBy('thn_setor', 'desc')
                ->orderBy('bln_setor', 'desc')
                ->cursor()
                ->each(function ($row) use ($file) {
                    fputcsv($file, [
                        $row->thn_setor,
                        $row->bln_setor,
                        $row->tgl_setor,
                        $row->npwp15,
                        $row->nama_wp,
                        $row->jenis,
                        $row->fungsi,
                        $row->kd_map,
                        $row->kd_bayar,
                        $row->masa_pajak,
                        $row->thn_pajak,
                        $row->jml_setor,
                        $row->ntpn,
                    ]);
                });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}