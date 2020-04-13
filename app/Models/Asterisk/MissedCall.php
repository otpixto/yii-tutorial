<?php

namespace App\Models\Asterisk;

use App\Models\Customer;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Model;

class MissedCall extends Model
{
    protected $table = 'missed_calls';
    public $timestamps = false;
    protected $nullable = [
        'call_id',
        'call_date',
    ];
    protected $fillable = [
        'provider_id',
        'phone',
        'call_id',
        'calls_count',
        'call_date',
    ];
    public function customer ()
    {
        return $this->belongsTo( Customer::class, 'phone', 'phone' );
    }
	public function provider ()
    {
        return $this->belongsTo( Provider::class );
    }
	public function scopeMineProvider ( $query )
    {
        return $query
			->where( function ( $q )
			{
                $mineCurrentProvider = \Auth::user()->providers->where( 'id', Provider::getCurrent()->id )->first();
			    $q
					->whereNull( static::getTable() . '.provider_id' );
			    if ( $mineCurrentProvider )
                {
                    $q
                        ->orWhere( static::getTable() . '.provider_id', '=', $mineCurrentProvider->id );
                }
                return $q;
			});
    }
}
