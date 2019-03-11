<?php

namespace App\Models;

use App\Traits\CommentsTrait;
use App\Traits\LogsTrait;
use App\Traits\NormalizeValues;
use App\Traits\TagsTrait;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;

class BaseModel extends Model
{

    use SoftDeletes, NormalizeValues, LogsTrait, TagsTrait, CommentsTrait;

    protected $connection = 'eds';

    const NOTHING = 0;
    const IGNORE_PROVIDER = 1;
    const IGNORE_ADDRESS = 2;
    const IGNORE_MANAGEMENT = 3;
    const IGNORE_STATUS = 4;
    const I_AM_OWNER = 5;

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $guarded = [
        'id'
    ];

    public function files ()
    {
        return $this->hasMany( File::class, 'model_id' )
            ->where( 'model_name', '=', static::class );
    }

    public function parent ()
    {
        return $this->belongsTo( $this->model_name, 'model_id' )
            ->withTrashed();
    }

    public function parentOriginal ()
    {
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
        return $this->belongsTo( Provider::class );
    }

    public function author ()
    {
        return $this->belongsTo( User::class );
    }

    public function user ()
    {
        return $this->belongsTo( User::class );
    }

    public static function create ( array $attributes = [] )
    {
        self::normalizeValues( $attributes );
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
        self::normalizeValues( $attributes );
        $res = $this->saveLogs( $attributes );
        if ( $res instanceof MessageBag )
        {
            return $res;
        }
        $this->fill( $attributes );
        $this->save();
        return $this;
    }

    public function canComment ()
    {
        return false;
    }

    public static function genHash ( $string )
    {
        return md5( mb_strtolower( trim( preg_replace( '/[^a-zA-ZА-Яа-я0-9]/iu', '', $string ) ) ) );
    }

    public function scopeWhereLike ( $query, $field, $value )
    {
        return $query
            ->where( $field, 'like', '%' . str_replace( ' ', '%', $value ) . '%' );
    }

    public function scopeOrWhereLike ( $query, $field, $value )
    {
        return $query
            ->orWhere( $field, 'like', '%' . str_replace( ' ', '%', $value ) . '%' );
    }

    public function scopeMineProvider ( $query )
    {
        return $query
			->where( function ( $q )
			{
				return $q
					->whereNull( static::getTable() . '.provider_id' )
					->orWhereHas( 'provider', function ( $provider )
					{
						return $provider
							->mine()
							->current();
					});
			});
    }

}
