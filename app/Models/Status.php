<?php

namespace App\Models;

class Status extends BaseModel
{

    protected $table = 'statuses';
    public static $_table = 'statuses';

    public static $name = 'Статус';

    protected $fillable = [
        'model_name',
        'status_code',
        'status_name'
    ];

    public static $rules = [
        'model_name'		        => 'required|string',
        'status_code'				=> 'required|string',
        'status_name'				=> 'required|string',
    ];

}
