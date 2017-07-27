<?php

namespace App\Models;

class Category extends BaseModel
{

    protected $table = 'categories';

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

    public static function create ( array $attributes = [] )
    {
        $new = new Category( $attributes );
        $new->save();
        return $new;
    }

}
