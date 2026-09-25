<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterfileWp extends Model
{
    protected $table = 'masterfile_wp';

    protected $primaryKey = 'npwp15';

    public $incrementing = false;

    protected $keyType = 'string';

    // Alias untuk Nama WP
    public function getNamaWpAttribute()
    {
        return $this->attributes['nama'] ?? '-';
    }

    // Alias untuk Jenis WP
    public function getJenisWpAttribute()
    {
        return $this->attributes['jenis'] ?? '-';
    }

    // Alias untuk Status WP
    public function getStatusWpAttribute()
    {
        return $this->attributes['status'] ?? '-';
    }

    // Alias untuk Nama AR
    public function getNamaArAttribute()
    {
        return $this->ar->nama ?? '-';
    }

    // Alias untuk Nama JS
    public function getNamaJsAttribute()
    {
        return $this->js->nama ?? '-';
    }

    // Relasi ke AR (Mengambil data Pegawai berdasarkan NIP & Tahun Saat Ini)
    public function ar()
    {
        return $this->belongsTo(Pegawai::class, 'nip_ar', 'nip')
            ->where('tahun', date('Y'));
    }

    // Relasi Jurusita (JS)
    public function js()
    {
        return $this->belongsTo(Pegawai::class, 'nip_js', 'nip')
            ->where('tahun', date('Y'));
    }

    // Relasi Detil Transaksi
    public function transaksi()
    {
        return $this->hasMany(DetilTransaksiWp::class, 'npwp15', 'npwp15');
    }
}
