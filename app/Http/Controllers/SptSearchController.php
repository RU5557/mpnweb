<?php

namespace App\Http\Controllers;

use App\Repositories\SptCoretaxRepository;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SptSearchController extends Controller
{
    public function __construct(protected SptCoretaxRepository $sptRepository) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['npwp', 'nama', 'masa1', 'masa2', 'thn_pajak', 'pembetulan', 'jenis_spt', 'status_spt', 'tgl_terima_mulai', 'tgl_terima_selesai', 'nip_ar']);
        $hasSearch = $request->has('has_search');

        $sptList = $hasSearch ? $this->sptRepository->searchSpt($filters, 20) : null;

        return view('pencarian.spt', [
            'sptList' => $sptList,
            'jenisSptList' => $this->sptRepository->getDistinctJenisSpt(),
            'statusSptList' => $this->sptRepository->getDistinctStatusSpt(),
            'pembetulanList' => $this->sptRepository->getDistinctPembetulan(),
            'arList' => $this->sptRepository->getDistinctAR(),
            'filters' => $filters,
            'hasSearch' => $hasSearch,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $filters = $request->only(['npwp', 'nama', 'masa1', 'masa2', 'thn_pajak', 'pembetulan', 'jenis_spt', 'status_spt', 'tgl_terima_mulai', 'tgl_terima_selesai', 'nip_ar']);

        return $this->sptRepository->exportSptCsv($filters);
    }
}
