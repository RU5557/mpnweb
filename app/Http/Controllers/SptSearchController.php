<?php

namespace App\Http\Controllers;

use App\Repositories\Contracts\SptCoretaxRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SptSearchController extends Controller
{
    protected SptCoretaxRepositoryInterface $sptRepository;

    public function __construct(SptCoretaxRepositoryInterface $sptRepository)
    {
        $this->sptRepository = $sptRepository;
    }

    public function index(Request $request): View
    {
        $filters = $request->only([
            'npwp',
            'nama',
            'masa1',
            'masa2',
            'thn_pajak',
            'pembetulan',
            'jenis_spt',
            'status_spt',
            'tgl_terima_mulai',
            'tgl_terima_selesai',
            'nip_ar',
        ]);

        $hasSearch = $request->has('has_search');

        // Hanya eksekusi kueri jika user sudah menekan tombol cari
        $sptList = $hasSearch ? $this->sptRepository->searchSpt($filters, 20) : null;

        $jenisSptList = $this->sptRepository->getDistinctJenisSpt();
        $statusSptList = $this->sptRepository->getDistinctStatusSpt();
        $arList = $this->sptRepository->getDistinctAR();

        return view('pencarian.spt', compact(
            'sptList',
            'jenisSptList',
            'statusSptList',
            'arList',
            'filters',
            'hasSearch'
        ));
    }
}
