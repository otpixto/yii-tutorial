<?php

namespace App\Models;

class Segment extends BaseModel
{

    protected $table = 'segments';
    public static $_table = 'segments';

    public static $name = 'Сегмент';

    protected $nullable = [
        'parent_id',
    ];

    protected $fillable = [
        'name',
        'parent_id',
        'provider_id',
        'segment_type_id',
    ];

    public function segmentType ()
    {
        return $this->belongsTo(SegmentType::class );
    }

    public function parent ()
    {
        return $this->belongsTo(Segment::class );
    }

    public function childs ()
    {
        return $this->hasMany(Segment::class, 'parent_id' );
    }

    public function buildings ()
    {
        return $this->hasMany(Building::class );
    }

    public function path ()
    {
        return $this->hasOne( SegmentPath::class );
    }

    public function getChildsIds ()
    {
        $childsIds = [ $this->id ];
        return $this->_getChildsIds( $childsIds );
    }

    private function _getChildsIds ( & $childsIds = [] )
    {
        foreach ( $this->childs as $child )
        {
            $childsIds[] = $child->id;
            $child->_getChildsIds( $childsIds );
        }
        return $childsIds;
    }

    public function scopeMine ( $query )
    {
        return $query
            ->mineProvider();
    }

    public function getName ( $withParent = false )
    {
        $name = '';
        if ( $withParent && $this->parent )
        {
            $name .= $this->parent->name . ', ';
        }
        $name .= $this->name;
        return $name;
    }

}
