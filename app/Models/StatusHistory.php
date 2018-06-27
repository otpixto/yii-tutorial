<?php

namespace App\Models;

class StatusHistory extends BaseModel
{

    protected $table = 'statuses_history';
    public static $_table = 'statuses_history';

    public static $name = 'История статусов';

    protected $fillable = [
        'author_id',
        'model_id',
        'model_name',
        'status_code',
        'status_name'
    ];

    public static $rules = [
        'author_id'			        => 'integer',
        'model_id'			        => 'required|integer',
        'model_name'		        => 'required|string',
        'status_code'				=> 'required|string',
        'status_name'				=> 'required|string',
    ];

}
