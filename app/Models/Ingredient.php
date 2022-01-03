<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use SoftDeletes;
    protected $table = 'ingredients';
    protected $fillable = ['product_id'];

    public function rawmat(){
        return $this->belongsTo(Rawmat::class)->orderBy('name');
    }
}
