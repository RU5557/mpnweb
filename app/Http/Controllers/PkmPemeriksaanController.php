<?php

namespace App\Http\Controllers;

use App\Repositories\PkmRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPemeriksaanController extends Controller
{
    public function __construct(
        protected PkmRepository $repository
    ) {}

    /**
     * Tampilkan Ringkasan PKM Pemeriksaan per Wajib Pajak
     */
    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $search = trim((string) $request->input('search', ''));
        $sortColumn = (string) $request->input('sort', 'total_akt_pemeriksaan');
        $sortDirection = (string) $request->input('direction', 'desc');
        $page = max(1, (int) $request->input('page', 1));

        try {
            // Panggil method getPaginatedPemeriksaan dari PkmRepository
            $pkmData = $this->repository->getPaginatedPemeriksaan(
                $tahun,
                $bulan,
                $search,
                $sortColumn,
                $sortDirection,
                $page
            );
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Pemeriksaan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'search' => $search,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Pemeriksaan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('penerimaan.pkmpemeriksaan', compact(
            'pkmData',
            'sortColumn',
            'sortDirection',
            'tahun',
            'bulan'
        ));
    }

    /**
     * Handle Export CSV Detil Transaksi Pemeriksaan
     */
    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $search = trim((string) $request->input('search', ''));

        // Panggil method exportPemeriksaanCsv dari PkmRepository
        return $this->repository->exportPemeriksaanCsv($tahun, $bulan, $search);
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
