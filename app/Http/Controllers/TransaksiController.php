<?php

namespace App\Http\Controllers;

use App\Repositories\TransaksiRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransaksiController extends Controller
{
    protected TransaksiRepository $transaksiRepository;

    public function __construct(TransaksiRepository $transaksiRepository)
    {
        $this->transaksiRepository = $transaksiRepository;
    }

    /**
     * Halaman Pencarian & Filter Transaksi / DRM
     */
    public function index(Request $request)
    {
        $tahun = (int) date('Y');
        $filters = $request->all();

        // 1. Ambil opsi dropdown yang sudah di-cache
        $dropdowns = $this->transaksiRepository->getFilterDropdownOptions($tahun);

        // 2. Ambil data transaksi paginasi
        $results = null;
        if ($request->has('has_search')) {
            $results = $this->transaksiRepository->searchTransaksiPaginated($filters, 20, $tahun);
            $results->appends($filters);
        }

        // 3. Return view dengan passing variabel filter
        return view('pencarian.transaksi', array_merge($dropdowns, [
            'results' => $results,
            'npwpInput' => $request->get('npwp'),
            'namaWp' => $request->get('nama'),
            'kdMap' => $request->get('kd_map'),
            'kdBayar' => $request->get('kd_bayar'),
            'tglSetorStart' => $request->get('tgl_setor_start'),
            'tglSetorEnd' => $request->get('tgl_setor_end'),
            'ntpn' => $request->get('ntpn'),
            'masa1Selected' => $request->get('masa1'),      // FILTER MASA 1
            'masa2Selected' => $request->get('masa2'),      // FILTER MASA 2
            'thnPajakSelected' => $request->get('thn_pajak'), // FILTER THN PAJAK
            'kotaSelected' => $request->get('kota'),
            'jenisWpSelected' => $request->get('jenis_wp'),
            'sektorSelected' => $request->get('sektor'),
            'seksiSelected' => $request->get('seksi_id'),
            'thnSetor' => $request->get('thn_setor', []),
            'blnSetor' => $request->get('bln_setor', []),
            'fungsi' => $request->get('fungsi', []),
            'nipAr' => $request->get('nip_ar', []),
            'nipJs' => $request->get('nip_js', []),
            'sortBy' => $request->get('sort_by', 'tgl_setor'),
            'sortOrder' => $request->get('sort_order', 'desc'),
        ]));
    }

    /**
     * Export CSV Transaksi
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $tahun = (int) date('Y');

        return $this->transaksiRepository->exportTransaksiCsv($request->all(), $tahun);
    }
}
