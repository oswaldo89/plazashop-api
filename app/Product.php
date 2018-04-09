<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'nombre',
        'precio',
        'categoriaId',
        'local',
        'telephone',
        'descripcion',
        'user_id'
    ];

    public function photos()
    {
        return $this->hasMany('App\ProductsPhoto');
    }
}
