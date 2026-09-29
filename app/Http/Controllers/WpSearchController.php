<?php

namespace App\Http\Controllers;

use App\Repositories\WpRepository;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WpSearchController extends Controller
{
    protected WpRepository $wpRepository;

    public function __construct(WpRepository $wpRepository)
    {
        $this->wpRepository = $wpRepository;
    }

    /**
     * Halaman & Pencarian Masterfile WP
     */
    public function searchMasterfile(Request $request)
    {
        $tahun = (int) date('Y');
        $filters = $request->all();

        // 1. Ambil pilihan Dropdown Filter dari Repository (Cached)
        $dropdowns = $this->wpRepository->getFilterDropdownOptions($tahun);

        // 2. Cek apakah ada pencarian
        $hasSearch = $request->has('has_search');

        // 3. Ambil data hasil pencarian paginasi jika form di-submit
        $results = null;
        if ($hasSearch) {
            $results = $this->wpRepository->searchMasterfilePaginated($filters, 20, $tahun);
            $results->appends($filters);
        }

        // 4. Tangkap variabel filter untuk dikirimkan ke Blade View
        // (Hapus baris `$results = 'results';` yang salah tadi)
        $npwpInput = trim((string) $request->get('npwp'));
        $namaWp = trim((string) $request->get('nama'));
        $klu = $request->get('klu');
        $kelurahan = $request->get('kelurahan');
        $kecamatan = $request->get('kecamatan');
        $jenis = $request->get('jenis');
        $status = $request->get('status');
        $tglDaftarAwal = $request->get('tgl_daftar_awal');
        $tglDaftarAkhir = $request->get('tgl_daftar_akhir');
        $nipAr = $request->get('nip_ar');
        $nipJs = $request->get('nip_js');
        $sortBy = $request->get('sort_by', 'nama');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        return view('pencarian.masterfile', array_merge($dropdowns, compact(
            'hasSearch', 'results', 'npwpInput', 'namaWp', 'klu', 'kelurahan', 'kecamatan',
            'jenis', 'status', 'tglDaftarAwal', 'tglDaftarAkhir', 'nipAr', 'nipJs',
            'sortBy', 'sortOrder'
        )));
    }

    /**
     * Export CSV Masterfile
     */
    public function exportMasterfileCsv(Request $request): StreamedResponse
    {
        set_time_limit(0);

        return $this->wpRepository->exportMasterfileCsv($request->all());
    }
}
