<?php

namespace App\Http\Controllers;

use App\Repositories\PkmRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPengawasanController extends Controller
{
    public function __construct(
        protected PkmRepository $repository
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
            // Panggil method getSummaryPengawasan & getDaftarSeksiPengawasan dari PkmRepository
            $pkmData = $this->repository->getSummaryPengawasan($tahun, $bulan, $seksiFilter, $sortColumn, $sortDirection);
            $daftarSeksi = $this->repository->getDaftarSeksiPengawasan();
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

        // Panggil method exportPengawasanCsv dari PkmRepository
        return $this->repository->exportPengawasanCsv($tahun, $bulan, $seksiFilter);
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
