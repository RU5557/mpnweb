<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DetilTransaksiExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    /**
     * Query data detil transaksi dari tabel mentah berdasarkan filter UI
     */
    public function query()
    {
        $query = DB::table('detil_transaksi_wp as dt')
            ->leftJoin('masterfile_wp as mw', 'dt.npwp15', '=', 'mw.npwp15')
            ->select(
                'dt.thn_setor',
                'dt.bln_setor',
                'dt.tgl_setor',
                'dt.npwp15',
                'dt.nama_wp',
                'mw.nip_ar',
                'dt.kd_map',
                'dt.kd_bayar',
                'dt.masa_pajak',
                'dt.thn_pajak',
                'dt.jml_setor',
                'dt.ntpn',
                'dt.no_pbk',
                'dt.jenis',
                'dt.fungsi'
            );

        // Filter 1: Tahun Setor
        if (!empty($this->filters['thn_setor'])) {
            $query->where('dt.thn_setor', $this->filters['thn_setor']);
        }

        // Filter 2: Bulan Setor
        if (!empty($this->filters['bln_setor'])) {
            $query->where('dt.bln_setor', $this->filters['bln_setor']);
        }

        // Filter 3: Fungsi PKM (PENGAWASAN / PEMERIKSAAN / PENAGIHAN)
        if (!empty($this->filters['fungsi'])) {
            $query->where('dt.fungsi', $this->filters['fungsi']);
        }

        // Filter 4: Jenis Transaksi (PPM / PKM)
        if (!empty($this->filters['jenis'])) {
            $query->where('dt.jenis', $this->filters['jenis']);
        }

        // Filter 5: NIP AR (jika ada filter spesifik dari UI)
        if (!empty($this->filters['nip_ar'])) {
            $query->where('mw.nip_ar', $this->filters['nip_ar']);
        }

        return $query->orderBy('dt.tgl_setor', 'desc');
    }

    /**
     * Judul Header Kolom Excel
     */
    public function headings(): array
    {
        return [
            'Tahun Setor',
            'Bulan Setor',
            'Tanggal Setor',
            'NPWP (15 Digit)',
            'Nama Wajib Pajak',
            'NIP AR',
            'Kode MAP',
            'Kode Bayar',
            'Masa Pajak',
            'Tahun Pajak',
            'Jumlah Setor (Rp)',
            'NTPN',
            'No PBK',
            'Jenis',
            'Fungsi'
        ];
    }

    /**
     * Formatting tiap baris data
     */
    public function map($row): array
    {
        return [
            $row->thn_setor,
            $row->bln_setor,
            $row->tgl_setor,
            "'" . $row->npwp15, // Kutip tunggal agar NPWP dibaca sebagai teks & angka 0 di depan tidak hilang
            $row->nama_wp,
            "'" . $row->nip_ar,
            $row->kd_map,
            $row->kd_bayar,
            $row->masa_pajak,
            $row->thn_pajak,
            $row->jml_setor,
            $row->ntpn,
            $row->no_pbk,
            $row->jenis,
            $row->fungsi
        ];
    }

    /**
     * Membaca per 2000 baris agar RAM server tidak overload
     */
    public function chunkSize(): int
    {
        return 2000;
    }
}