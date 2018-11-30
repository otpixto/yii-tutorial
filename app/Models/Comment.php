<?php

namespace App\Models;

class Comment extends BaseModel
{

    protected $table = 'comments';
    public static $_table = 'comments';

    public static $name = 'Комментарий';

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

}
