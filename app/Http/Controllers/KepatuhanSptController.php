<?php

namespace App\Http\Controllers;

use App\Repositories\SptCoretaxRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KepatuhanSptController extends Controller
{
    public function __construct(protected SptCoretaxRepository $sptRepository) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['npwp', 'nama', 'jenis_spt', 'tgl_terima_mulai', 'tgl_terima_selesai', 'thn_pajak', 'nip_ar']);

        // Kosongkan jika string kosong, jangan paksa default tahun
        if (isset($filters['thn_pajak']) && $filters['thn_pajak'] === '') {
            unset($filters['thn_pajak']);
        }

        $matrixData = $this->sptRepository->getMatrixKepatuhanData($filters, 15);
        $optJenisSpt = $this->sptRepository->getDistinctJenisSpt();
        $tahunAr = ! empty($filters['thn_pajak']) ? (int) $filters['thn_pajak'] : (int) date('Y');
        $optAr = $this->sptRepository->getDistinctAR($tahunAr);

        return view('kepatuhan.pelaporan', compact('matrixData', 'optJenisSpt', 'optAr', 'filters'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only(['npwp', 'nama', 'jenis_spt', 'tgl_terima_mulai', 'tgl_terima_selesai', 'thn_pajak', 'nip_ar']);
        if (isset($filters['thn_pajak']) && $filters['thn_pajak'] === '') {
            unset($filters['thn_pajak']);
        }

        return $this->sptRepository->exportPivotCsv($filters);
    }
}
