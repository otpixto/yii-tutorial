<?php

namespace App\Models;

class Tag extends BaseModel
{

    protected $table = 'tags';

    protected $fillable = [
        'model_id',
        'model_name',
        'text'
    ];
	
	public static $rules = [
        'model_id'			=> 'required|integer',
		'model_name'		=> 'required|string',
        'text'				=> 'required|string',
    ];

    public function parent ()
    {
        return $this->belongsTo( $this->model_name, 'model_id' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new Tag( $attributes );
        $new->save();
        return $new;
    }

}
