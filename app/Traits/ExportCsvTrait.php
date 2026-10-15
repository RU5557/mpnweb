<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\StreamedResponse;

trait ExportCsvTrait
{
    /**
     * Stream CSV output secara bertahap langsung ke browser
     */
    public function streamCsvDownload(string $filename, array $headers, iterable $dataCursor, callable $rowCallback): StreamedResponse
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($headers, $dataCursor, $rowCallback) {
            $handle = fopen('php://output', 'w');

            // Tambahkan BOM untuk UTF-8 (agar karakter khusus terbaca dengan benar di Excel)
            fwrite($handle, "\xEF\xBB\xBF");

            // Header Kolom CSV
            fputcsv($handle, $headers);

            // Stream Baris Data
            foreach ($dataCursor as $item) {
                fputcsv($handle, $rowCallback($item));
            }

            fclose($handle);
        }, 200, $responseHeaders);
    }
}
