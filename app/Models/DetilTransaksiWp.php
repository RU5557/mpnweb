<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetilTransaksiWp extends Model
{
    protected $table = 'detil_transaksi_wp';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'tgl_setor' => 'date',
        'thn_setor' => 'integer',
        'bln_setor' => 'integer',
        'thn_pajak' => 'integer',
        'masa1' => 'string',
        'masa2' => 'string',
        'jml_setor' => 'decimal:2',
    ];

    /**
     * Relasi ke Masterfile WP
     */
    public function masterfile(): BelongsTo
    {
        return $this->belongsTo(MasterfileWp::class, 'npwp15', 'npwp15');
    }
}
