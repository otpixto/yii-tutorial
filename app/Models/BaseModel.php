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

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function getModelName ()
    {
        return self::$name ?? null;
    }

    public function addComment ( $text )
    {
        $comment = Comment::create([
            'model_id'     	            => $this->id,
            'model_name'	            => get_class( $this ),
            'origin_model_id'			=> $this->id,
            'origin_model_name'		    => get_class( $this ),
            'text'                      => $text
        ]);
        $comment->save();
        $res = $comment->addLog( 'Добавлен комментарий' );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }
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
        $res = $tag->addLog( 'Добавлен тег' );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }
        return $tag;
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
        if ( isset( $attributes['model_name'], $attributes['model_id'] ) )
        {
            $model = new $attributes['model_name'];
            $exists = $model->where( 'id', '=', $attributes['model_id'] )->first();
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
        $new->addLog( 'Создана запись' );
        return $new;
    }

    public function edit ( array $attributes = [] )
    {
        $res = $this->saveLogs( $attributes );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }
        $this->fill( $attributes );
        $this->save();
        return $this;
    }

    public function saveLogs ( array $newValues = [] )
    {
        $oldValues = $this->getAttributes();
        foreach ( $newValues as $field => $val )
        {
            if ( ! isset( $oldValues[ $field ] ) || $oldValues[ $field ] == $val ) continue;
            $log = $this->saveLog( $field, $oldValues[ $field ], $val );
            if ( $log instanceof MessageBag )
            {
                return $log;
            }
        }
    }

    public function saveLog ( $field, $oldValue, $newValue )
    {
        $log = $this->addLog( '"' . $field . '" изменено с "' . $oldValue . '" на "' . $newValue . '"' );
        if ( $log instanceof MessageBag )
        {
            return $log;
        }
    }

    public function addLog ( $text )
    {
        $log = Log::create([
            'model_id'      => $this->id,
            'model_name'    => get_class( $this ),
            'text'          => $text
        ]);
        if ( $log instanceof MessageBag )
        {
            return $log;
        }
        $log->save();
    }

}
