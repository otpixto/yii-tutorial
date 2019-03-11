<?php

namespace App\Models;

class SegmentType extends BaseModel
{

    protected $table = 'segments_types';
    public static $_table = 'segments_types';

    public static $name = 'Тип сегмента';

    protected $fillable = [
        'name',
    ];

    public function segments ()
    {
        return $this->hasMany(Segment::class );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->mineProvider();
    }

}
