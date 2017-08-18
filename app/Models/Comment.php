<?php

namespace App\Models;

class Comment extends BaseModel
{

    protected $table = 'comments';

    protected $fillable = [
        'model_id',
		'model_name',
        'text'
    ];
	
	public static $rules = [
        'model_id'			=> 'required|integer',
		'model_name'		=> 'required|string',
        'text'				=> 'required|string',
        'files'             => 'array'
    ];

}
