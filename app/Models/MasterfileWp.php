<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterfileWp extends Model
{
    protected $table = 'masterfile_wp';
    protected $primaryKey = 'npwp15';
    public $incrementing = false;
    protected $keyType = 'string';

    public function ar()
    {
        return $this->belongsTo(Pegawai::class, 'nip_ar', 'nip')
                    ->where('tahun', date('Y'));
    }

    public function transaksi()
    {
        return $this->hasMany(DetilTransaksiWp::class, 'npwp15', 'npwp15');
    }
}