<?php

namespace App\Services;

use App\Repositories\MfwpRepository;
use App\Traits\ExportCsvTrait;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MfwpExportService
{
    use ExportCsvTrait;

    public function __construct(
        protected MfwpRepository $mfwpRepository
    ) {}

    public function exportToCsv(array $filters): StreamedResponse
    {
        $filename = 'export_mfwp_'.date('Ymd_His').'.csv';

        $headers = [
            'NPWP 15', 'NPWP 16', 'NAMA', 'ALAMAT', 'KELURAHAN', 'KECAMATAN',
            'KOTA', 'PROPINSI', 'JENIS', 'BENTUK HUKUM', 'STATUS', 'KLU',
            'TANGGAL DAFTAR', 'NIK', 'TELP', 'NIP AR', 'NAMA AR', 'SEKSI', 'NIP JS', 'NAMA JSPN',
        ];

        $cursor = $this->mfwpRepository->getCursorForExport($filters);

        return $this->streamCsvDownload($filename, $headers, $cursor, function ($row) {
            return [
                $row->npwp15,
                $row->npwp16,
                $row->nama,
                $row->alamat,
                $row->kelurahan,
                $row->kecamatan,
                $row->kota,
                $row->propinsi,
                $row->jenis,
                $row->bentuk_hukum,
                $row->status,
                $row->klu,
                $row->tanggal_daftar,
                $row->nik,
                $row->telp,
                $row->nip_ar,
                $row->nama_ar ?? '-',
                $row->nama_seksi ?? '-',
                $row->nip_js,
                $row->nama_jspn ?? '-',
            ];
        });
    }
}
