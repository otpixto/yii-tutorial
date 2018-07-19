<?php

namespace App\Models;

class Category extends BaseModel
{

    protected $table = 'categories';
    public static $_table = 'categories';

    public static $name = 'Категория';

    public static $rules = [
        'name'              => 'required|string|max:255',
    ];

    protected $fillable = [
        'name',
    ];

    public function types ()
    {
        return $this->hasMany( 'App\Models\Type' );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

}
