<?php

namespace App\Models;

class Executor extends BaseModel
{

    protected $table = 'managements_executors';
    public static $_table = 'managements_executors';

    public static $name = 'Исполнитель';

    protected $fillable = [
        'management_id',
        'name',
    ];
	
	public static $rules = [
        'management_id'	    => 'required|integer',
        'name'				=> 'required|string',
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

    public function scopeMine ( $query )
    {
        return $query
            ->whereHas( 'management', function ( $management )
            {
                return $management->mine();
            });
    }

}
