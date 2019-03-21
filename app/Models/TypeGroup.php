<?php

namespace App\Models;

class TypeGroup extends BaseModel
{

    protected $table = 'types_groups';
    public static $_table = 'types_groups';

    public static $name = 'Группа классификатора';

    protected $fillable = [
        'provider_id',
        'name',
    ];

    public function scopeMine ( $query )
    {
        return $query
            ->mineProvider();
    }

}