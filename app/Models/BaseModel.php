<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{

    use SoftDeletes;

    public function addComment ( $text )
    {

        $comment = Comment::create([
            'model_id'     	=> $this->id,
            'model_name'	=> get_class( $this ),
            'text'          => $text
        ]);

        return $comment;

    }

    public function addTag ( $text )
    {

        $comment = Tag::create([
            'model_id'     	=> $this->id,
            'model_name'	=> get_class( $this ),
            'text'          => $text
        ]);

        return $comment;

    }

    public function comments ()
    {
        return $this->hasMany( 'App\Models\Comment', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function tags ()
    {
        return $this->hasMany( 'App\Models\Tag', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

}
