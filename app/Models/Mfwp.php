<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mfwp extends Model
{
    protected $table = 'mfwp';

    protected $primaryKey = 'npwp15';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $guarded = [];

    /**
     * Relasi AR (Jabatan = 5, Tahun saat ini)
     */
    public function ar(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip_ar', 'nip')
            ->where('jabatan', '5')
            ->where('tahun', date('Y'));
    }

    /**
     * Relasi JSPN (Jabatan = 11, Tahun saat ini)
     */
    public function jspn(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'nip_js', 'nip')
            ->where('jabatan', '11')
            ->where('tahun', date('Y'));
    }

    /**
     * Relasi KLU Master
     */
    public function kluMaster(): BelongsTo
    {
        return $this->belongsTo(Klu::class, 'klu', 'kd_klu');
    }
}
