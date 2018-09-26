<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;

class BaseModel extends Model
{

    use SoftDeletes;

    const IGNORE_PROVIDER = 1;
    const IGNORE_ADDRESS = 2;
    const IGNORE_MANAGEMENT = 3;
    const IGNORE_STATUS = 4;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $guarded = [
        'id'
    ];

    public function addComment ( $text )
    {
        $comment = Comment::create([
            'model_id'     	            => $this->id,
            'model_name'	            => get_class( $this ),
            'origin_model_id'			=> $this->id,
            'origin_model_name'		    => get_class( $this ),
            'text'                      => $text
        ]);
        if ( $comment instanceof MessageBag )
        {
            return $comment;
        }
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
        if ( $tag instanceof MessageBag )
        {
            return $tag;
        }
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
        return $this->belongsTo( $this->model_name, 'model_id' )
            ->withTrashed();
    }

    public function parentOriginal ()
    {
        if ( ! Schema::hasColumn( $this->getTable(), 'origin_model_name' ) ) return null;
        return $this->belongsTo( $this->origin_model_name, 'origin_model_id' );
    }

    public function childs ()
    {
        $model_name = get_class( $this );
        return $this->hasMany( $model_name, 'model_id' )
            ->where( 'model_name', '=', $model_name );
    }

    public function provider ()
    {
        return $this->belongsTo( 'App\Models\Provider' );
    }

    public function author ()
    {
        return $this->belongsTo( 'App\User' );
    }

    public static function create ( array $attributes = [] )
    {
        if ( isset( $attributes[ 'model_name' ], $attributes[ 'model_id' ] ) )
        {
            $model = new $attributes[ 'model_name' ];
            $exists = $model->where( 'id', '=', $attributes[ 'model_id' ] )
                ->first();
            if ( ! $exists )
            {
                return new MessageBag( [ 'Некорректные данные' ] );
            }
        }
        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/\D/', '', $attributes[ 'phone' ] ), - 10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/\D/', '', $attributes[ 'phone2' ] ), - 10 );
        }
        $new = new static( $attributes );
        if ( Schema::hasColumn( $new->getTable(), 'author_id' ) && ! $new->author_id && \Auth::user() )
        {
            $new->author_id = \Auth::user()->id;
        }
        if ( Schema::hasColumn( $new->getTable(), 'provider_id' ) && ! $new->provider_id )
        {
            if ( Provider::getCurrent() )
            {
                $new->provider_id = Provider::$current->id;
            }
            else
            {
                $providers = Provider::mine()->get();
                if ( $providers->count() == 1 )
                {
                    $new->provider_id = $providers->first()->id;
                }
            }
        }
        return $new;
    }

    public function edit ( array $attributes = [] )
    {
        if ( ! empty( $attributes[ 'phone' ] ) )
        {
            $attributes[ 'phone' ] = mb_substr( preg_replace( '/\D/', '', $attributes[ 'phone' ] ), - 10 );
        }
        if ( ! empty( $attributes[ 'phone2' ] ) )
        {
            $attributes[ 'phone2' ] = mb_substr( preg_replace( '/\D/', '', $attributes[ 'phone2' ] ), - 10 );
        }
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
            if ( ! isset( $oldValues[ $field ] ) || $oldValues[ $field ] == $val || in_array( $field, $this->guarded ) ) continue;
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

    public function addLog ( $text, $author_id = null )
    {
        $log = Log::create([
            'author_id'     => $author_id,
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

    public function canComment ()
    {
        return false;
    }

    public static function genHash ( $string )
    {
        return md5( mb_strtolower( trim( preg_replace( '/[^a-zA-ZА-Яа-я0-9]/iu', '', $string ) ) ) );
    }

}
