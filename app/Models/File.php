<?php

namespace App\Models;

class File extends BaseModel
{

    const PASS = 'LcPBjX';

    protected $table = 'files';
    public static $_table = 'files';

    public static $name = 'Файл';

    protected $fillable = [
        'model_id',
        'model_name',
        'path',
        'name'
    ];

    public function getToken ()
    {
        return md5( $this->id . self::PASS );
    }

}
