<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class File extends BaseModel
{

    protected $table = 'files';

    protected $fillable = [
        'model_id',
        'model_name',
        'path'
    ];

    public static $rules = [
        'model_id'			=> 'required|integer',
        'model_name'		=> 'required|string',
        'path'				=> 'required|string',
    ];

}
