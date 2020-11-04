<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawmatLog extends Model
{
    use SoftDeletes;
    protected $table = 'rawmat_logs';
}
