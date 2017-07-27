<?php

namespace App\Models;

class Status extends BaseModel
{

    protected $table = 'statuses';

    protected $fillable = [
        'model_name',
        'status_code',
        'status_name'
    ];

    public static $rules = [
        'model_name'		        => 'required|string',
        'status_code'				=> 'required|string',
        'status_name'				=> 'required|string',
    ];

    public function parent ()
    {
        return $this->belongsTo( $this->model_name, 'model_id' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new Status( $attributes );
        $new->save();
        return $new;
    }

}
