<?php

namespace App\Models;

class Tag extends BaseModel
{

    protected $table = 'tags';

    public static $name = 'Тег';

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

}
