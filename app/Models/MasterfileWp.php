<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterfileWp extends Model
{
    protected $table = 'masterfile_wp';

    protected $primaryKey = 'npwp15';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    // Relasi ke Pegawai (AR)
    public function ar(?int $tahun = null): BelongsTo
    {
        $tahun = $tahun ?? (int) date('Y');

        return $this->belongsTo(Pegawai::class, 'nip_ar', 'nip')
            ->where('tahun', $tahun);
    }

    // Relasi ke Pegawai (JS)
    public function js(?int $tahun = null): BelongsTo
    {
        $tahun = $tahun ?? (int) date('Y');

        return $this->belongsTo(Pegawai::class, 'nip_js', 'nip')
            ->where('tahun', $tahun);
    }

    /**
     * Accessor untuk $item->nama_ar
     */
    public function getNamaArAttribute(): ?string
    {
        return $this->ar?->nama;
    }

    /**
     * Accessor untuk $item->nama_js
     */
    public function getNamaJsAttribute(): ?string
    {
        return $this->js?->nama;
    }

    // Relasi ke KLU
    public function kluData(): BelongsTo
    {
        return $this->belongsTo(Klu::class, 'klu', 'kd_klu');
    }

    // Relasi ke Transaksi
    public function transaksi(): HasMany
    {
        return $this->hasMany(DetilTransaksiWp::class, 'npwp15', 'npwp15');
    }
}
