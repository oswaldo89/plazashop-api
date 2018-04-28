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
        'descripcion',
        'user_id',
        'uniq'
    ];
    protected $casts = [
        'categoriaId' => 'integer'
    ];

    public function photos()
    {
        return $this->hasMany('App\ProductsPhoto');
    }
}
