<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Category extends Model
{
    use SoftDeletes;
    protected $table = 'categories';

    public function tenant(){
        return $this->belongsTo(Tenant::class);
    }
    public function products(){
        return $this->hasMany(Product::class);
    }
}
