<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;

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

        $comment->save();

        return $comment;

    }

    public function addTag ( $text )
    {

        $tag = Tag::create([
            'model_id'     	=> $this->id,
            'model_name'	=> get_class( $this ),
            'text'          => $text
        ]);

        $tag->save();

        return $tag;

    }

    public function addLog ( $text )
    {

        $log = Log::create([
            'model_id'     	=> $this->id,
            'model_name'	=> get_class( $this ),
            'text'          => $text
        ]);

        $log->save();

        return $log;

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

    public function logs ()
    {
        return $this->hasMany( 'App\Models\Log', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function files ()
    {
        return $this->hasMany( 'App\Models\File', 'model_id' )
            ->where( 'model_name', '=', get_class( $this ) );
    }

    public function parent ()
    {
        return $this->belongsTo( $this->model_name, 'model_id' );
    }

    public function childs ()
    {
        $model_name = get_class( $this );
        return $this->hasMany( $model_name, 'model_id' )
            ->where( 'model_name', '=', $model_name );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public static function create ( array $attributes = [] )
    {

        if ( !empty( $attributes['model_name'] ) )
        {
            $model = new $attributes['model_name'];
            $exists = $model->where( 'id', '=',$attributes['model_id'] )->first();
            if ( ! $exists )
            {
                return new MessageBag( [ 'Некорректные данные' ] );
            }
        }

        $new = new static( $attributes );

        if ( Schema::hasColumn( $new->getTable(), 'author_id' ) )
        {
            $new->author_id = Auth::user()->id;
        }

        return $new;

    }

}
