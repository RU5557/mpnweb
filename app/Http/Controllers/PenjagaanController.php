<?php

namespace App\Http\Controllers;

use App\Repositories\PenjagaanRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenjagaanController extends Controller
{
    public function __construct(
        protected PenjagaanRepository $repository
    ) {}

    /**
     * Resolusi filter fungsi dari Request
     */
    private function resolveFungsi(Request $request): array
    {
        $fungsiOptions = $this->repository->getFungsiOptions();

        if ($request->has('fungsi')) {
            $fungsi = (array) $request->input('fungsi', []);

            return array_values(array_filter($fungsi));
        }

        return $fungsiOptions->toArray();
    }

    // 1. Penjagaan Bulanan
    public function bulanan(Request $request)
    {
        $fungsiOptions = $this->repository->getFungsiOptions();
        $fungsi = $this->resolveFungsi($request);

        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $data = $this->repository->getSummaryBulanan($fungsi, $tahunIni, $tahunLalu);

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $dataTahunLalu = [];
        $dataTahunIni = [];

        for ($m = 1; $m <= 12; $m++) {
            $dataTahunLalu[] = (float) ($data['lalu'][$m] ?? 0);
            $dataTahunIni[] = (float) ($data['ini'][$m] ?? 0);
        }

        return view('penerimaan.penjagaan.bulanan', compact(
            'months', 'dataTahunLalu', 'dataTahunIni', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunLalu'
        ));
    }

    // 2. Penjagaan Harian
    public function harian(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsiOptions = $this->repository->getFungsiOptions();
        $fungsi = $this->resolveFungsi($request);

        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $data = $this->repository->getSummaryHarian($bulan, $fungsi, $tahunIni, $tahunLalu);

        $days = range(1, 31);
        $dataTahunLalu = [];
        $dataTahunIni = [];

        foreach ($days as $day) {
            $dataTahunIni[] = (float) ($data['ini'][$day] ?? 0);
            $dataTahunLalu[] = (float) ($data['lalu'][$day] ?? 0);
        }

        return view('penerimaan.penjagaan.harian', compact(
            'days', 'dataTahunLalu', 'dataTahunIni', 'bulan', 'fungsiOptions', 'fungsi', 'tahunIni', 'tahunLalu'
        ));
    }

    // 3. Penjagaan vs Bulan Lalu
    public function vsBulanLalu(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsiOptions = $this->repository->getFungsiOptions();
        $fungsi = $this->resolveFungsi($request);

        $tahunIni = (int) date('Y');
        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunBulanLalu = $bulan == 1 ? $tahunIni - 1 : $tahunIni;

        $data = $this->repository->getSummaryVsBulanLalu($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu, $fungsi);

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

    // EXPORTS
    public function exportBulananCsv(Request $request): StreamedResponse
    {
        $fungsi = $this->resolveFungsi($request);
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $filename = 'penjagaan_bulanan_detil_'.date('Ymd_His').'.csv';

        return $this->repository->exportCsv($filename, function ($query) use ($fungsi, $tahunIni, $tahunLalu) {
            $query->whereIn('thn_setor', [$tahunLalu, $tahunIni]);
            if (! empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }
            $query->orderBy('thn_setor', 'desc')->orderBy('bln_setor', 'desc');
        });
    }

    public function exportHarianCsv(Request $request): StreamedResponse
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsi = $this->resolveFungsi($request);
        $tahunIni = (int) date('Y');
        $tahunLalu = $tahunIni - 1;

        $filename = 'penjagaan_harian_detil_bln_'.$bulan.'_'.date('Ymd_His').'.csv';

        return $this->repository->exportCsv($filename, function ($query) use ($bulan, $fungsi, $tahunIni, $tahunLalu) {
            $query->whereIn('thn_setor', [$tahunLalu, $tahunIni])
                ->where('bln_setor', $bulan);
            if (! empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }
            $query->orderBy('tgl_setor', 'desc');
        });
    }

    public function exportVsBulanLaluCsv(Request $request): StreamedResponse
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $fungsi = $this->resolveFungsi($request);

        $tahunIni = (int) date('Y');
        $bulanLalu = $bulan == 1 ? 12 : $bulan - 1;
        $tahunBulanLalu = $bulan == 1 ? $tahunIni - 1 : $tahunIni;

        $filename = 'penjagaan_vs_bulan_lalu_bln_'.$bulan.'_'.date('Ymd_His').'.csv';

        return $this->repository->exportCsv($filename, function ($query) use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu, $fungsi) {
            $query->where(function ($q) use ($bulan, $bulanLalu, $tahunIni, $tahunBulanLalu) {
                $q->where(function ($q1) use ($bulan, $tahunIni) {
                    $q1->where('thn_setor', $tahunIni)->where('bln_setor', $bulan);
                })->orWhere(function ($q2) use ($bulanLalu, $tahunBulanLalu) {
                    $q2->where('thn_setor', $tahunBulanLalu)->where('bln_setor', $bulanLalu);
                });
            });

            if (! empty($fungsi)) {
                $query->whereIn('fungsi', $fungsi);
            }

            $query->orderBy('tgl_setor', 'desc');
        });
    }
}
