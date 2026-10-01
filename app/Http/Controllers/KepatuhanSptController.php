<?php

namespace App\Http\Controllers;

use App\Repositories\SptCoretaxRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KepatuhanSptController extends Controller
{
    public function __construct(
        protected SptCoretaxRepository $sptRepository
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only([
            'npwp',
            'nama',
            'jenis_spt',
            'tgl_terima_mulai',
            'tgl_terima_selesai',
            'thn_pajak',
            'nip_ar',
        ]);

        // Default Tahun Pajak jika kosong
        if (empty($filters['thn_pajak'])) {
            $filters['thn_pajak'] = date('Y');
        }

        $matrixData = $this->sptRepository->getMatrixKepatuhanData($filters, 15);
        $optJenisSpt = $this->sptRepository->getDistinctJenisSpt();
        $optAr = $this->sptRepository->getDistinctAR((int) $filters['thn_pajak']);

        return view('kepatuhan.pelaporan', compact('matrixData', 'optJenisSpt', 'optAr', 'filters'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only([
            'npwp',
            'nama',
            'jenis_spt',
            'tgl_terima_mulai',
            'tgl_terima_selesai',
            'thn_pajak',
            'nip_ar',
        ]);

        // Pastikan default tahun pajak konsisten dengan tampilan web
        if (empty($filters['thn_pajak'])) {
            $filters['thn_pajak'] = date('Y');
        }

        return $this->sptRepository->exportPivotCsv($filters);
    }
}
