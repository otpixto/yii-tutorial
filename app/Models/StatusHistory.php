<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;

class StatusHistory extends BaseModel
{

    protected $table = 'statuses_history';

    protected $fillable = [
        'author_id',
        'model_id',
        'model_name',
        'status_code',
        'status_name'
    ];

    public static $rules = [
        'author_id'			        => 'integer',
        'model_id'			        => 'required|integer',
        'model_name'		        => 'required|string',
        'status_code'				=> 'required|string',
        'status_name'				=> 'required|string',
    ];

    public function parent ()
    {
        return $this->belongsTo( $this->model_name, 'model_id' );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public static function create ( array $attributes = [] )
    {
        $new = new StatusHistory( $attributes );
        if ( empty( $new->author_id ) )
        {
            $new->author_id = Auth::user()->id;
        }
        $new->save();
        return $new;
    }

}
