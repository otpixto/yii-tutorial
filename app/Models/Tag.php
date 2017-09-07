<?php

namespace App\Models;

use Illuminate\Support\MessageBag;

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

}
