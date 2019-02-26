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
        'reply_id',
        'text'
    ];

    protected $nullable = [
        'reply_id',
    ];

}
