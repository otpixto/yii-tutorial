<?php

namespace App\Models;

class Log extends BaseModel
{

    protected $table = 'logs';
    public static $_table = 'logs';

    public static $name = 'Системный лог';

    protected $fillable = [
        'author_id',
        'model_id',
		'model_name',
        'text'
    ];

    protected $nullable = [
        'author_id',
        'model_id',
        'model_name',
    ];

}
