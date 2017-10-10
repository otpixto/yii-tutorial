<?php

namespace App\Models;

class Log extends BaseModel
{

    protected $table = 'logs';

    public static $name = 'Системный лог';

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
