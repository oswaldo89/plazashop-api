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
        'uniq',
        'cuantity',
        'latitude',
        'longitude',
        'Pais',
        'Estado',
        'Municipio',
        'Colonia'
    ];

    protected $casts = [
        'categoriaId' => 'integer',
        'cuantity' => 'integer'
    ];

    public function photos()
    {
        return $this->hasMany('App\ProductsPhoto');
    }
}
