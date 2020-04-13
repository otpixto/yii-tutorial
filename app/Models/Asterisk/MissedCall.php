<?php

namespace App\Models\Asterisk;

use App\Models\Customer;
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
        'phone',
        'call_id',
        'calls_count',
        'call_date',
    ];


    public function customer ()
    {
        return $this->belongsTo( Customer::class, 'phone', 'phone' );
    }

}
