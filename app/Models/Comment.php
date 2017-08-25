<?php

namespace App\Models;

class Comment extends BaseModel
{

    protected $table = 'comments';

    protected $fillable = [
        'model_id',
		'model_name',
        'origin_model_id',
        'origin_model_name',
        'text'
    ];

    protected $nullable = [
        'origin_model_id',
        'origin_model_name',
    ];
	
	public static $rules = [
        'model_id'			        => 'required|integer',
		'model_name'		        => 'required|string',
        'origin_model_id'			=> 'nullable|integer',
        'origin_model_name'		    => 'nullable|string',
        'text'				        => 'required|string',
        'files'                     => 'array'
    ];

}
