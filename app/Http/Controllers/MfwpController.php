<?php

namespace App\Http\Controllers;

use App\Models\Klu;
use App\Repositories\MfwpRepository;
use App\Services\MfwpExportService;
use Illuminate\Http\Request;

class MfwpController extends Controller
{
    public function __construct(
        protected MfwpRepository $mfwpRepository,
        protected MfwpExportService $mfwpExportService
    ) {}

    /**
     * Halaman Utama Pencarian MFWP
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'npwp', 'nama', 'kd_klu', 'kelurahan', 'kecamatan',
            'jenis', 'status', 'tgl_daftar_awal', 'tgl_daftar_akhir',
            'nip_ar', 'id_seksi', 'nip_js',
        ]);

        // Query data pencarian
        $wajibPajakList = $this->mfwpRepository->searchPaginated($filters, 15);

        // Options untuk Filter Dropdown
        $filterOptions = [
            'kelurahan' => $this->mfwpRepository->getDistinctKelurahan(),
            'kecamatan' => $this->mfwpRepository->getDistinctKecamatan(),
            'jenis' => $this->mfwpRepository->getDistinctJenis(),
            'status' => $this->mfwpRepository->getDistinctStatus(),
            'arList' => $this->mfwpRepository->getArList(),
            'seksiList' => $this->mfwpRepository->getSeksiPengawasanList(),
            'jspnList' => $this->mfwpRepository->getJspnList(),
        ];

        return view('mfwp.index', compact('wajibPajakList', 'filterOptions', 'filters'));
    }

    /**
     * Export CSV Stream
     */
    public function exportCsv(Request $request)
    {
        $filters = $request->only([
            'npwp', 'nama', 'kd_klu', 'kelurahan', 'kecamatan',
            'jenis', 'status', 'tgl_daftar_awal', 'tgl_daftar_akhir',
            'nip_ar', 'id_seksi', 'nip_js',
        ]);

        return $this->mfwpExportService->exportToCsv($filters);
    }

    /**
     * Endpoint AJAX Lookup KLU
     */
    public function searchKlu(Request $request)
    {
        $keyword = $request->get('q', '');

        $klu = Klu::query()
            ->when($keyword, function ($q) use ($keyword) {
                $q->where('kd_klu', 'like', "%{$keyword}%")
                    ->orWhere('nm_klu', 'like', "%{$keyword}%");
            })
            ->limit(20)
            ->get(['kd_klu', 'nm_klu']);

        return response()->json($klu);
    }
}
