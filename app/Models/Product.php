<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $table = 'products';
    protected $cast = [
        'use_rawmat' => 'boolean'
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function order_logs(){
        return $this->hasMany(OrderLog::class);
    }

    public function ingredients(){
        return $this->hasMany(Ingredient::class);
    }
}
