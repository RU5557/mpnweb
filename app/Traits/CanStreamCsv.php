<?php

namespace App\Traits;

use Symfony\Component\HttpFoundation\StreamedResponse;

trait CanStreamCsv
{
    /**
     * Stream CSV Response dengan UTF-8 BOM dan auto-flush
     */
    protected function streamCsvResponse(string $filename, array $headers, callable $callback): StreamedResponse
    {
        $responseHeaders = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($headers, $callback) {
            set_time_limit(0);

            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // UTF-8 BOM untuk Microsoft Excel

            if (! empty($headers)) {
                fputcsv($file, $headers);
            }

            // Jalankan callback untuk menulis isi baris CSV
            $callback($file);

            fclose($file);
        }, 200, $responseHeaders);
    }
}
