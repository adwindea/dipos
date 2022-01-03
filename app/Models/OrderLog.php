<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderLog extends Model
{
    use SoftDeletes;
    protected $table = 'order_logs';

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
