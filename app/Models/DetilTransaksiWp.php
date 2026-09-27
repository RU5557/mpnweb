<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetilTransaksiWp extends Model
{
    protected $table = 'detil_transaksi_wp';

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * Relasi ke Masterfile WP
     */
    public function masterfile(): BelongsTo
    {
        return $this->belongsTo(MasterfileWp::class, 'npwp15', 'npwp15');
    }
}
