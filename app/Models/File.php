<?php

namespace App\Models;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

    public function download () : BinaryFileResponse
    {
        return response()->download( storage_path( 'app/' . $this->path ), $this->name );
    }

    public function getContents ()
    {
        return file_get_contents( storage_path( 'app/' . $this->path ) );
    }

}
