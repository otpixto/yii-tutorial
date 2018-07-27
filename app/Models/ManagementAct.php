<?php

namespace App\Models;

class ManagementAct extends BaseModel
{

    protected $table = 'managements_acts';
    public static $_table = 'managements_acts';

    public static $name = 'Акты';

    public static $rules = [
        'management_id'         => 'required|integer',
        'name'                  => 'required',
        'content'               => 'required',
    ];

    protected $fillable = [
        'management_id',
        'name',
        'content',
    ];

    public function management ()
    {
        return $this->belongsTo( 'App\Models\Management' );
    }

}
