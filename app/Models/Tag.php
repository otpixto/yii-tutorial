<?php

namespace App\Models;

class Tag extends BaseModel
{

    protected $table = 'tags';
    public static $_table = 'tags';

    public static $name = 'Тег';

    protected $fillable = [
        'model_id',
        'model_name',
        'text'
    ];

}
