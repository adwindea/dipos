<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $table = 'orders';

    public function promotion(){
        return $this->belongsTo(Promotion::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
