<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryMartPenerimaan extends Model
{
    use HasFactory;

    protected $table = 'summary_mart_penerimaan';

    protected $guarded = ['id'];

    /**
     * Scope untuk memfilter berdasarkan rentang tahun dan bulan
     */
    public function scopePeriode($query, int $tahun, int $bulanAwal, int $bulanAkhir)
    {
        return $query->where('thn_setor', $tahun)
            ->whereBetween('bln_setor', [$bulanAwal, $bulanAkhir]);
    }
}
