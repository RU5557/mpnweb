<?php

namespace App\Http\Controllers;

use App\Repositories\PkmPenagihanRepository;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PkmPenagihanController extends Controller
{
    public function __construct(
        protected PkmPenagihanRepository $repository
    ) {}

    /**
     * Tampilkan Summary PKM Penagihan per JSPN & Flag SKP
     */
    public function index(Request $request)
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $dspcFilter = $this->resolveDspcFilter($request);

        $sortColumn = (string) $request->input('sort', 'nip_jspn');
        $sortDirection = (string) $request->input('direction', 'asc');

        try {
            $pkmData = $this->repository->getSummaryPkm(
                $tahun,
                $bulan,
                $dspcFilter,
                $sortColumn,
                $sortDirection
            );
        } catch (QueryException $e) {
            Log::error('Gagal memuat summary PKM Penagihan.', [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'message' => $e->getMessage(),
            ]);

            abort(503, 'Data PKM Penagihan sedang tidak tersedia. Silakan coba lagi.');
        }

        return view('penerimaan.pkmpenagihan', compact(
            'pkmData',
            'sortColumn',
            'sortDirection',
            'tahun',
            'bulan',
            'dspcFilter'
        ));
    }

    /**
     * Handle Export CSV Detil Transaksi Penagihan
     */
    public function exportDetil(Request $request): StreamedResponse
    {
        [$tahun, $bulan] = $this->resolvePeriod($request);
        $dspcFilter = $this->resolveDspcFilter($request);

        return $this->repository->exportDetilCsv($tahun, $bulan, $dspcFilter);
    }

    private function resolveDspcFilter(Request $request): string
    {
        $filter = (string) $request->input('dspc_filter', '');

        return in_array($filter, ['DSPC', 'NON-DSPC'], true) ? $filter : '';
    }

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
