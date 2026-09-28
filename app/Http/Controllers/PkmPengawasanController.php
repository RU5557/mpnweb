<?php

namespace App\Http\Controllers;

use App\Repositories\PkmPengawasanRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPengawasanController extends Controller
{
    public function __construct(
        protected PkmPengawasanRepository $repository
    ) {}

    /**
     * Tampilkan Ringkasan PKM Pengawasan per Seksi & AR
     */
    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $seksiFilter = trim((string) $request->input('seksi', ''));
        $sortColumn = (string) $request->input('sort', 'nama_seksi');
        $sortDirection = (string) $request->input('direction', 'asc');

        try {
            $pkmData = $this->repository->getSummaryPkm($tahun, $bulan, $seksiFilter, $sortColumn, $sortDirection);
            $daftarSeksi = $this->repository->getDaftarSeksi();
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Pengawasan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Pengawasan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('penerimaan.pkmpengawasan', compact(
            'pkmData',
            'daftarSeksi',
            'sortColumn',
            'sortDirection',
            'tahun',
            'bulan',
            'seksiFilter'
        ));
    }

    /**
     * Handle Export CSV Detil Transaksi Pengawasan
     */
    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $seksiFilter = trim((string) $request->input('seksi', ''));

        return $this->repository->exportDetilCsv($tahun, $bulan, $seksiFilter);
    }

    /**
     * Helper resolusi periode tahun & bulan
     */
    private function resolvePeriod(Request $request): array
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $bulan = (int) $request->input('bulan', date('n'));

        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = (int) date('Y');
        }

        if ($bulan < 1 || $bulan > 12) {
            $bulan = (int) date('n');
        }

        return [$tahun, $bulan];
    }
}
