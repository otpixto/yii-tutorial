<?php

namespace App\Models;

class SegmentPath extends BaseModel
{

    protected $table = 'segments_paths';
    public static $_table = 'segments_paths';

    public static $name = 'Абсолютный путь';

    protected $fillable = [
        'ticket_id',
        'status_code',
        'status_name'
    ];

    public function segment ()
    {
        return $this->belongsTo( Segment::class );
    }

}