<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\MessageBag;

class File extends BaseModel
{

    const PASS = 'LcPBjX';

    protected $table = 'files';

    protected $fillable = [
        'model_id',
        'model_name',
        'path',
        'name'
    ];

    public static $rules = [
        'model_id'			=> 'required|integer',
        'model_name'		=> 'required|string',
        'path'				=> 'required|string',
        'name'              => 'required|string'
    ];

    public function getToken ()
    {
        return md5( $this->id . self::PASS );
    }

}
