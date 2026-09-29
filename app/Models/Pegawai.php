<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    public $timestamps = false;

    // Scope untuk memfilter Jabatan AR (5) atau JS (11)
    public function scopeJabatan($query, int $jabatan, ?int $tahun = null)
    {
        $tahun = $tahun ?? (int) date('Y');

        return $query->where('jabatan', (string) $jabatan)
            ->where('tahun', $tahun);
    }
}
