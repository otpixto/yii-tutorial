<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class Comment extends Model
{

    protected $table = 'comments';

    protected $fillable = [
        'author_id',
        'model_id',
		'model_name',
        'text'
    ];
	
	public static $rules = [
        'author_id'			=> 'integer',
        'model_id'			=> 'required|integer',
		'model_name'		=> 'required|string',
        'text'				=> 'required|string',
    ];

	public function author ()
    {
        return $this->belongsTo( 'App\User' );
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

    public static function create ( array $attributes = [] )
    {
		
		$model = new $attributes['model_name'];
		$exists = $model->where( 'id', '=',$attributes['model_id'] )->first();

		if ( ! $exists )
		{
			return new MessageBag( [ 'Некорректные данные' ] );
		}
		
        $new = new Comment( $attributes );
        if ( empty( $new->author_id ) )
        {
            $new->author_id = Auth::user()->id;
        }
        $new->save();
        return $new;
    }

}
