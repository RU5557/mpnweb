<?php

// app/Http/Controllers/WpController.php

namespace App\Http\Controllers;

use App\Repositories\WpRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WpController extends Controller
{
    protected WpRepository $wpRepository;

    public function __construct(WpRepository $wpRepository)
    {
        $this->wpRepository = $wpRepository;
    }

    /**
     * Endpoint Pencarian Masterfile WP
     */
    public function searchMasterfile(Request $request): JsonResponse
    {
        $request->validate([
            'keyword' => 'required|string|min:3',
            'limit' => 'integer|max:100',
            'offset' => 'integer',
        ]);

        $data = $this->wpRepository->searchMasterfile(
            $request->input('keyword'),
            $request->input('limit', 20),
            $request->input('offset', 0)
        );

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Endpoint Pencarian Detil Transaksi WP
     */
    public function searchTransactions(Request $request): JsonResponse
    {
        $filters = $request->only(['keyword', 'thn_setor', 'bln_setor', 'fungsi', 'nip_ar', 'nip_js']);
        $limit = (int) $request->input('limit', 20);
        $offset = (int) $request->input('offset', 0);

        $data = $this->wpRepository->searchTransactions($filters, $limit, $offset);

        return response()->json(['status' => 'success', 'data' => $data]);
    }

    /**
     * Endpoint Opsi Dropdown Filter Form
     */
    public function getFilterOptions(): JsonResponse
    {
        $options = $this->wpRepository->getDropdownOptions();

        return response()->json(['status' => 'success', 'data' => $options]);
    }
}
