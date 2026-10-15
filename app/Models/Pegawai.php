<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pegawai extends Model
{
    protected $table = 'pegawai';

    public $timestamps = false;

    protected $guarded = [];

    public function seksiDetail(): BelongsTo
    {
        return $this->belongsTo(Seksi::class, 'seksi', 'id');
    }
}
