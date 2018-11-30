<?php

namespace App\Traits;

use App\Models\Log;
use Illuminate\Support\MessageBag;

trait LogsTrait
{

    public function logs ()
    {
        return $this->hasMany( 'App\Models\Log', 'model_id' )
            ->where( 'model_name', '=', static::class );
    }

    public function addLog ( $text )
    {
        if ( isset( $this->id ) )
        {
            $log = Log::create([
                'model_id'      => $this->id,
                'model_name'    => static::class,
                'text'          => $text,
            ]);
        }
        else
        {
            $log = Log::create([
                'text'          => $text,
            ]);
        }
        if ( $log instanceof MessageBag )
        {
            return $log;
        }
        $log->save();
        return $log;
    }

    public function saveLog ( $field, $oldValue, $newValue )
    {
        $log = $this->addLog( '"' . $field . '" изменено с "' . $oldValue . '" на "' . $newValue . '"' );
        if ( $log instanceof MessageBag )
        {
            return $log;
        }
        return $log;
    }

    public function saveLogs ( array $newValues = [] )
    {
        $oldValues = $this->getAttributes();
        $guarded = $this->guarded ?? [];
        foreach ( $newValues as $field => $val )
        {
            if ( ! isset( $oldValues[ $field ] ) || $oldValues[ $field ] == $val || in_array( $field, $guarded ) ) continue;
            $log = $this->saveLog( $field, $oldValues[ $field ], $val );
            if ( $log instanceof MessageBag )
            {
                return $log;
            }
        }
    }

}
