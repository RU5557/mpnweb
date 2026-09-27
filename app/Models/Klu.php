<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Klu extends Model
{
    protected $table = 'klu';

    protected $primaryKey = 'kd_klu';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;
}
