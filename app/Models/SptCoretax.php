<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SptCoretax extends Model
{
    use HasFactory;

    protected $table = 'spt_coretax';

    public $timestamps = false;

    protected $fillable = [
        'tahun',
        'bulan',
        'bulan_data',
        'kanwil',
        'kpp',
        'npwp',
        'nama',
        'masa1',
        'masa2',
        'thn_pajak',
        'jenis_spt',
        'nomor_tanda_terima',
        'tgl_terima',
        'nop',
        'status_spt',
        'pembetulan',
        'kanal_pelaporan',
        'kd_kpp_administrasi',
        'kpp_administrasi',
        'kd_kpp_penerima',
        'kpp_penerima',
    ];

    /**
     * Relasi ke Masterfile WP melalui klausa NPWP
     */
    public function masterfile()
    {
        return $this->belongsTo(MasterfileWp::class, 'npwp', 'npwp16');
    }
}
